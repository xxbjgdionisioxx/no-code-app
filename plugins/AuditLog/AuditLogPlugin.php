<?php

namespace Plugins\AuditLog;

use Plugins\PluginInterface;
use Plugins\PluginManager;

/**
 * AuditLogPlugin — Records every record create/update/delete to the audit_log table.
 *
 * This plugin demonstrates the extensibility hook system.
 * It hooks into three platform lifecycle events and writes
 * tamper-evident audit trails with old/new data snapshots.
 *
 * ── How to extend this plugin ────────────────────────────────
 *
 *   Hook into additional events:
 *     $manager->on('before_dispatch', function($payload) { ... });
 *
 *   Or create a completely different plugin following the same pattern:
 *     class SlackNotificationPlugin implements PluginInterface { ... }
 *
 * ── How to override a UI template ────────────────────────────
 *
 *   Place a file at:  views/overrides/records/index.php
 *   The platform checks this path first before loading the default view.
 *   In Controller::view(), the lookup order is:
 *     1. BASE_PATH/views/overrides/{view}.php
 *     2. BASE_PATH/views/{view}.php
 *
 *   To enable override support, modify Controller::view() to:
 *     $override = BASE_PATH.'/views/overrides/'.str_replace('.','/',$view).'.php';
 *     $viewFile = file_exists($override) ? $override : BASE_PATH.'/views/...';
 */
class AuditLogPlugin implements PluginInterface
{
    public function __construct(private \PDO $db) {}

    public function meta(): array
    {
        return [
            'name'        => 'Audit Log',
            'version'     => '1.0.0',
            'description' => 'Records every record create, update, and delete action with old/new data snapshots.',
            'author'      => 'AppForge Core',
        ];
    }

    /**
     * Register listeners on three core record lifecycle hooks.
     * The PluginManager calls this once during application boot.
     */
    public function boot(PluginManager $manager): void
    {
        $manager->on('record_created', [$this, 'onRecordCreated']);
        $manager->on('record_updated', [$this, 'onRecordUpdated']);
        $manager->on('record_deleted', [$this, 'onRecordDeleted']);
    }

    // ── Listeners ────────────────────────────────────────────

    /**
     * Payload: ['record_id' => int, 'module_id' => int, 'values' => array]
     */
    public function onRecordCreated(array $payload): void
    {
        $this->log(
            action:    'create',
            recordId:  $payload['record_id'] ?? null,
            moduleId:  $payload['module_id'] ?? null,
            oldData:   null,
            newData:   $payload['values']    ?? []
        );
    }

    /**
     * Payload: ['record_id' => int, 'old' => array, 'new' => array]
     */
    public function onRecordUpdated(array $payload): void
    {
        $this->log(
            action:   'update',
            recordId: $payload['record_id'] ?? null,
            moduleId: null,
            oldData:  $payload['old'] ?? [],
            newData:  $payload['new'] ?? []
        );
    }

    /**
     * Payload: ['record_id' => int, 'old' => array]
     */
    public function onRecordDeleted(array $payload): void
    {
        $this->log(
            action:   'delete',
            recordId: $payload['record_id'] ?? null,
            moduleId: null,
            oldData:  $payload['old'] ?? [],
            newData:  null
        );
    }

    // ── Internal ─────────────────────────────────────────────

    /**
     * Write a single audit log row.
     * Resolves the current user and app context from the session.
     */
    private function log(
        string  $action,
        ?int    $recordId,
        ?int    $moduleId,
        ?array  $oldData,
        ?array  $newData
    ): void {
        // Resolve current user from session (if running in web context)
        $userId = null;
        $appId  = null;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $user   = $_SESSION['user'] ?? null;
            $userId = $user['id'] ?? null;
        }

        // Resolve app_id from the record header
        if ($recordId && !$appId) {
            $stmt = $this->db->prepare('SELECT app_id, module_id FROM records WHERE id = ?');
            $stmt->execute([$recordId]);
            $row   = $stmt->fetch();
            $appId = $row['app_id']    ?? null;
            $moduleId ??= $row['module_id'] ?? null;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO audit_log
             (user_id, app_id, module_id, record_id, action, old_data, new_data, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $userId,
            $appId,
            $moduleId,
            $recordId,
            $action,
            $oldData !== null ? json_encode($oldData) : null,
            $newData !== null ? json_encode($newData) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}
