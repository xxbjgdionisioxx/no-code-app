<?php

namespace Engine;

/**
 * WorkflowEngine — If/then automation rule evaluation.
 *
 * Workflows are defined per-module and triggered after record create/update/delete.
 * Each workflow has conditions and actions. When conditions match, actions execute.
 *
 * Supported condition operators: equals, not_equals, contains, gt, lt, is_empty
 * Supported actions: send_email, update_field, notification
 */
class WorkflowEngine
{
    public function __construct(private \PDO $db) {}

    // ── Trigger Entry Point ──────────────────────────────────

    /**
     * Evaluate all active workflows for a module after a record event.
     *
     * @param int    $moduleId
     * @param string $event     'create' | 'update' | 'delete'
     * @param array  $values    Current field values keyed by field slug
     * @param int    $recordId
     */
    public function evaluate(int $moduleId, string $event, array $values, int $recordId): void
    {
        $workflows = $this->getActiveWorkflows($moduleId, $event);

        foreach ($workflows as $workflow) {
            if ($this->conditionsPass($workflow, $values)) {
                $this->executeActions($workflow['id'], $values, $recordId);
            }
        }
    }

    // ── Workflow CRUD ─────────────────────────────────────────

    public function createWorkflow(int $moduleId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO workflows (module_id, name, trigger_on, conditions, condition_logic)
             VALUES (:module_id, :name, :trigger_on, :conditions, :condition_logic)'
        );
        $stmt->execute([
            ':module_id'       => $moduleId,
            ':name'            => $data['name'],
            ':trigger_on'      => $data['trigger_on']      ?? 'create_update',
            ':conditions'      => json_encode($data['conditions']      ?? []),
            ':condition_logic' => $data['condition_logic'] ?? 'AND',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function addAction(int $workflowId, string $type, array $config, int $order = 0): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO workflow_actions (workflow_id, action_type, config, sort_order)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$workflowId, $type, json_encode($config), $order]);
        return (int)$this->db->lastInsertId();
    }

    public function listWorkflows(int $moduleId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM workflows WHERE module_id = ? ORDER BY id');
        $stmt->execute([$moduleId]);
        $wfs = $stmt->fetchAll();
        foreach ($wfs as &$wf) {
            $wf['conditions'] = $wf['conditions'] ? json_decode($wf['conditions'], true) : [];
            $wf['actions']    = $this->getActions($wf['id']);
        }
        return $wfs;
    }

    public function deleteWorkflow(int $workflowId): bool
    {
        return $this->db->prepare('DELETE FROM workflows WHERE id=?')->execute([$workflowId]);
    }

    // ── Condition Evaluation ─────────────────────────────────

    /**
     * Returns true if all (AND) or any (OR) conditions are satisfied.
     */
    private function conditionsPass(array $workflow, array $values): bool
    {
        $conditions = $workflow['conditions'] ?? [];
        $logic      = $workflow['condition_logic'] ?? 'AND';

        // No conditions = always runs
        if (empty($conditions)) return true;

        $results = [];
        foreach ($conditions as $cond) {
            $fieldSlug = $cond['field_slug'] ?? '';
            $operator  = $cond['operator']   ?? 'equals';
            $target    = $cond['value']       ?? '';
            $actual    = $values[$fieldSlug] ?? '';

            $results[] = $this->evaluateCondition($operator, $actual, $target);
        }

        return ($logic === 'OR')
            ? in_array(true, $results, true)
            : !in_array(false, $results, true);
    }

    private function evaluateCondition(string $operator, mixed $actual, mixed $target): bool
    {
        return match ($operator) {
            'equals'      => (string)$actual === (string)$target,
            'not_equals'  => (string)$actual !== (string)$target,
            'contains'    => str_contains((string)$actual, (string)$target),
            'gt'          => is_numeric($actual) && is_numeric($target) && $actual > $target,
            'lt'          => is_numeric($actual) && is_numeric($target) && $actual < $target,
            'gte'         => is_numeric($actual) && is_numeric($target) && $actual >= $target,
            'lte'         => is_numeric($actual) && is_numeric($target) && $actual <= $target,
            'is_empty'    => $actual === null || $actual === '',
            'is_not_empty'=> $actual !== null && $actual !== '',
            default       => false,
        };
    }

    // ── Action Execution ─────────────────────────────────────

    private function executeActions(int $workflowId, array $values, int $recordId): void
    {
        $actions = $this->getActions($workflowId);

        foreach ($actions as $action) {
            $config = $action['config'] ? json_decode($action['config'], true) : [];

            match ($action['action_type']) {
                'send_email'    => $this->actionSendEmail($config, $values, $recordId),
                'update_field'  => $this->actionUpdateField($config, $values, $recordId),
                'notification'  => $this->actionNotification($config, $values, $recordId),
                default         => null,
            };
        }
    }

    /**
     * Action: Send an email using PHP's mail().
     * Config: {"to": "{{email_slug}}", "subject": "...", "body": "..."}
     * Supports {{field_slug}} template substitution in subject and body.
     */
    private function actionSendEmail(array $config, array $values, int $recordId): void
    {
        $to      = $this->interpolate($config['to']      ?? '', $values, $recordId);
        $subject = $this->interpolate($config['subject'] ?? 'Workflow Notification', $values, $recordId);
        $body    = $this->interpolate($config['body']    ?? '', $values, $recordId);

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return;

        $headers = "From: noreply@appforge.local\r\nContent-Type: text/html; charset=UTF-8\r\n";
        @mail($to, $subject, nl2br(htmlspecialchars($body)), $headers);
    }

    /**
     * Action: Update a specific field value on the triggering record.
     * Config: {"field_id": 5, "value": "Processed"}
     */
    private function actionUpdateField(array $config, array $values, int $recordId): void
    {
        $fieldId   = (int)($config['field_id'] ?? 0);
        $rawVal    = $config['value'] ?? '';

        if (!$fieldId) return;

        // 1. Interpolate placeholders {{field_slug}}
        $interpolated = $this->interpolate($rawVal, $values, $recordId);

        // 2. Evaluate as math if it contains operators
        $newValue = $this->evaluateMath($interpolated);

        // Update EAV row
        $stmt = $this->db->prepare(
            'INSERT INTO record_values (record_id, field_id, value) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );
        $stmt->execute([$recordId, $fieldId, $newValue]);

        // Refresh JSON snapshot
        $snap = $this->db->prepare(
            'SELECT f.slug, rv.value FROM record_values rv
             INNER JOIN fields f ON f.id = rv.field_id
             WHERE rv.record_id = ?'
        );
        $snap->execute([$recordId]);
        $data = array_column($snap->fetchAll(), 'value', 'slug');

        $this->db->prepare('UPDATE records SET data = ? WHERE id = ?')
                 ->execute([json_encode($data), $recordId]);
    }

    /**
     * Simple math evaluator for workflow formulas.
     * Supports +, -, *, /, and parentheses.
     * SECURITY: Only allows numbers and basic operators.
     */
    private function evaluateMath(string $expression): string
    {
        // If it doesn't look like math (contains letters other than numbers/operators), return as-is
        if (!preg_match('/^[0-9\.\+\-\*\/\(\)\s]+$/', $expression)) {
            return $expression;
        }

        try {
            // Use eval safely by pre-filtering with regex above
            // We use @ to suppress division by zero or other math errors
            $result = @eval("return $expression;");
            return $result !== false ? (string)$result : $expression;
        } catch (\Throwable $e) {
            return $expression;
        }
    }

    /**
     * Action: Create an in-app notification for all app admins.
     * Config: {"message": "Record {{id}} was updated", "user_id": null}
     */
    private function actionNotification(array $config, array $values, int $recordId): void
    {
        $message = $this->interpolate($config['message'] ?? 'Workflow triggered.', $values, $recordId);
        $userId  = (int)($config['user_id'] ?? 0);

        if ($userId > 0) {
            $stmt = $this->db->prepare(
                'INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)'
            );
            $stmt->execute([$userId, 'Workflow Notification', $message]);
        } else {
            // Notify all platform admins
            $adminStmt = $this->db->prepare('SELECT id FROM users WHERE is_admin = 1');
            $adminStmt->execute();
            $ins = $this->db->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
            foreach ($adminStmt->fetchAll() as $admin) {
                $ins->execute([$admin['id'], 'Workflow Notification', $message]);
            }
        }
    }

    // ── Template Interpolation ───────────────────────────────

    /**
     * Replace {{field_slug}} placeholders in action config templates.
     * Also supports {{id}} for the record ID.
     */
    private function interpolate(string $template, array $values, int $recordId): string
    {
        $template = str_replace('{{id}}', $recordId, $template);
        foreach ($values as $slug => $value) {
            $template = str_replace('{{' . $slug . '}}', (string)$value, $template);
        }
        return $template;
    }

    // ── Private Query Helpers ─────────────────────────────────

    private function getActiveWorkflows(int $moduleId, string $event): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM workflows
             WHERE module_id = ? AND is_active = 1
             AND (trigger_on = ? OR trigger_on = "create_update")'
        );
        $stmt->execute([$moduleId, $event]);
        $wfs = $stmt->fetchAll();
        foreach ($wfs as &$wf) {
            $wf['conditions'] = $wf['conditions'] ? json_decode($wf['conditions'], true) : [];
        }
        return $wfs;
    }

    private function getActions(int $workflowId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM workflow_actions WHERE workflow_id = ? ORDER BY sort_order ASC'
        );
        $stmt->execute([$workflowId]);
        return $stmt->fetchAll();
    }
}
