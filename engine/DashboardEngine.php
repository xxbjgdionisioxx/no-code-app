<?php

namespace Engine;

/**
 * DashboardEngine — Widget data aggregation and computation.
 *
 * Widget types:
 *  count       — COUNT(*) of records in a module (with optional filters)
 *  sum         — SUM of a numeric field
 *  average     — AVG of a numeric field
 *  bar_chart   — Aggregated counts/sums grouped by a dropdown field
 *  pie_chart   — Same as bar_chart, rendered differently by Chart.js
 */
class DashboardEngine
{
    public function __construct(private \PDO $db) {}

    // ── Widget CRUD ──────────────────────────────────────────

    public function createWidget(int $appId, int $userId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO dashboard_widgets
             (app_id, user_id, title, widget_type, module_id, field_id, filters, chart_color, width)
             VALUES (:app_id, :user_id, :title, :widget_type, :module_id, :field_id, :filters, :chart_color, :width)'
        );
        $stmt->execute([
            ':app_id'      => $appId,
            ':user_id'     => $userId,
            ':title'       => $data['title'],
            ':widget_type' => $data['widget_type'],
            ':module_id'   => $data['module_id'],
            ':field_id'    => $data['field_id'] ?? null,
            ':filters'     => isset($data['filters']) ? json_encode($data['filters']) : null,
            ':chart_color' => $data['chart_color'] ?? '#6366f1',
            ':width'       => (int)($data['width'] ?? 4),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getWidget(int $widgetId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM dashboard_widgets WHERE id=?');
        $stmt->execute([$widgetId]);
        $w = $stmt->fetch();
        if (!$w) return null;
        $w['filters'] = $w['filters'] ? json_decode($w['filters'], true) : [];
        return $w;
    }

    public function listWidgets(int $appId, int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT dw.*, m.name as module_name, f.name as field_name
             FROM dashboard_widgets dw
             INNER JOIN modules m ON m.id = dw.module_id
             LEFT JOIN fields f ON f.id = dw.field_id
             WHERE dw.app_id = ? AND dw.user_id = ?
             ORDER BY dw.position_y ASC, dw.position_x ASC'
        );
        $stmt->execute([$appId, $userId]);
        $widgets = $stmt->fetchAll();
        foreach ($widgets as &$w) {
            $w['filters'] = $w['filters'] ? json_decode($w['filters'], true) : [];
        }
        return $widgets;
    }

    public function deleteWidget(int $widgetId): bool
    {
        return $this->db->prepare('DELETE FROM dashboard_widgets WHERE id=?')->execute([$widgetId]);
    }

    // ── Data Computation ─────────────────────────────────────

    /**
     * Compute and return the data payload for a single widget.
     * This is called by DashboardController via AJAX for live updates.
     */
    public function compute(array $widget): array
    {
        $filters = $widget['filters'] ?? [];

        return match ($widget['widget_type']) {
            'count'     => $this->computeCount($widget, $filters),
            'sum'       => $this->computeAggregate('SUM', $widget, $filters),
            'average'   => $this->computeAggregate('AVG', $widget, $filters),
            'bar_chart' => $this->computeGrouped($widget, $filters),
            'pie_chart' => $this->computeGrouped($widget, $filters),
            default     => ['value' => 0],
        };
    }

    /**
     * COUNT(*) with optional EAV-based filters.
     */
    private function computeCount(array $widget, array $filters): array
    {
        [$sql, $params] = $this->buildFilteredQuery($widget['module_id'], $filters);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM records r {$sql}");
        $stmt->execute($params);
        return ['value' => (int)$stmt->fetchColumn(), 'type' => 'count'];
    }

    /**
     * SUM or AVG on a numeric field across filtered records.
     */
    private function computeAggregate(string $fn, array $widget, array $filters): array
    {
        if (!$widget['field_id']) return ['value' => 0, 'type' => strtolower($fn)];

        [$filterSql, $params] = $this->buildFilteredQuery($widget['module_id'], $filters);

        $sql = "SELECT {$fn}(CAST(rv.value AS DECIMAL(20,4))) as result
                FROM record_values rv
                INNER JOIN records r ON r.id = rv.record_id
                {$filterSql}
                AND rv.field_id = ?";
        $params[] = $widget['field_id'];


        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $val = $stmt->fetchColumn();

        return ['value' => round((float)$val, 2), 'type' => strtolower($fn)];
    }

    /**
     * GROUP BY a field value to produce chart labels + data.
     * Used for both bar and pie charts.
     */
    private function computeGrouped(array $widget, array $filters): array
    {
        if (!$widget['field_id']) return ['labels' => [], 'data' => [], 'type' => $widget['widget_type']];

        [$filterSql, $params] = $this->buildFilteredQuery($widget['module_id'], $filters);

        $sql = "SELECT rv.value as label, COUNT(*) as count
                FROM record_values rv
                INNER JOIN records r ON r.id = rv.record_id {$filterSql}
                AND rv.field_id = ?
                GROUP BY rv.value
                ORDER BY count DESC
                LIMIT 20";
        $params[] = $widget['field_id'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return [
            'labels' => array_column($rows, 'label'),
            'data'   => array_map(fn($r) => (int)$r['count'], $rows),
            'type'   => $widget['widget_type'],
        ];
    }

    /**
     * Build the WHERE/JOIN fragment for filter conditions.
     *
     * Each filter: {field_id, operator, value}
     * Returns ['WHERE clause fragment', $params]
     */
    private function buildFilteredQuery(int $moduleId, array $filters): array
    {
        $params = [$moduleId];
        $sql    = 'WHERE r.module_id = ?';

        foreach ($filters as $i => $filter) {
            $fieldId  = (int)($filter['field_id'] ?? 0);
            $operator = $filter['operator']   ?? 'equals';
            $value    = $filter['value']       ?? '';

            if (!$fieldId) continue;

            $alias   = "rv_f{$i}";
            $sql    .= " AND EXISTS (
                SELECT 1 FROM record_values {$alias}
                WHERE {$alias}.record_id = r.id AND {$alias}.field_id = ?
                AND {$alias}.value ";
            $params[] = $fieldId;

            switch ($operator) {
                case 'equals':
                    $sql    .= '= ?)';
                    $params[] = $value;
                    break;
                case 'not_equals':
                    $sql    .= '!= ?)';
                    $params[] = $value;
                    break;
                case 'contains':
                    $sql    .= 'LIKE ?)';
                    $params[] = "%{$value}%";
                    break;
                case 'gt':
                    $sql    .= '> ?)';
                    $params[] = $value;
                    break;
                case 'lt':
                    $sql    .= '< ?)';
                    $params[] = $value;
                    break;
                default:
                    $sql .= '= ?)';
                    $params[] = $value;
            }
        }

        return [$sql, $params];
    }
}
