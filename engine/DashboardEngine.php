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
             (app_id, user_id, title, widget_type, module_id, field_id, filters, chart_color, width, settings)
             VALUES (:app_id, :user_id, :title, :widget_type, :module_id, :field_id, :filters, :chart_color, :width, :settings)'
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
            ':settings'    => isset($data['settings']) ? json_encode($data['settings']) : null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateWidget(int $widgetId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE dashboard_widgets
             SET title = :title, widget_type = :widget_type, module_id = :module_id,
                 field_id = :field_id, filters = :filters, chart_color = :chart_color, 
                 width = :width, settings = :settings
             WHERE id = :id'
        );
        return $stmt->execute([
            ':id'          => $widgetId,
            ':title'       => $data['title'],
            ':widget_type' => $data['widget_type'],
            ':module_id'   => $data['module_id'],
            ':field_id'    => $data['field_id'] ?? null,
            ':filters'     => isset($data['filters']) ? json_encode($data['filters']) : null,
            ':chart_color' => $data['chart_color'] ?? '#6366f1',
            ':width'       => (int)($data['width'] ?? 4),
            ':settings'    => isset($data['settings']) ? json_encode($data['settings']) : null,
        ]);
    }

    public function getWidget(int $widgetId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM dashboard_widgets WHERE id=?');
        $stmt->execute([$widgetId]);
        $w = $stmt->fetch();
        if (!$w) return null;
        $w['filters']  = $w['filters']  ? json_decode($w['filters'], true)  : [];
        $w['settings'] = $w['settings'] ? json_decode($w['settings'], true) : [];
        return $w;
    }

    public function listWidgets(int $appId, int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT dw.*, m.name as module_name, m.slug as module_slug, f.name as field_name
             FROM dashboard_widgets dw
             INNER JOIN modules m ON m.id = dw.module_id
             LEFT JOIN fields f ON f.id = dw.field_id
             WHERE dw.app_id = ? AND dw.user_id = ?
             ORDER BY dw.position_y ASC, dw.position_x ASC'
        );
        $stmt->execute([$appId, $userId]);
        $widgets = $stmt->fetchAll();
        foreach ($widgets as &$w) {
            $w['filters']  = $w['filters']  ? json_decode($w['filters'], true)  : [];
            $w['settings'] = $w['settings'] ? json_decode($w['settings'], true) : [];
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

        $result = match ($widget['widget_type']) {
            'count'     => $this->computeCount($widget, $filters),
            'sum'       => $this->computeAggregate('SUM', $widget, $filters),
            'average'   => $this->computeAggregate('AVG', $widget, $filters),
            'bar_chart'   => $this->computeGrouped($widget, $filters),
            'pie_chart'   => $this->computeGrouped($widget, $filters),
            'trend_chart' => $this->computeTrend($widget, $filters),
            'progress_bar'=> $this->computeProgress($widget, $filters),
            'top_list'    => $this->computeTopList($widget, $filters),
            default       => ['value' => 0],
        };

        // Inject advanced settings into result (e.g. drill-down URL)
        $result['settings'] = $widget['settings'] ?? [];
        return $result;
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
     * SUM or AVG on a numeric field stored in records.data JSON column.
     */
    private function computeAggregate(string $fn, array $widget, array $filters): array
    {
        if (!$widget['field_id']) return ['value' => 0, 'type' => strtolower($fn)];

        // Get the field slug so we can JSON_EXTRACT it
        $slug = $this->getFieldSlug((int)$widget['field_id']);
        if (!$slug) return ['value' => 0, 'type' => strtolower($fn)];

        [$filterSql, $params] = $this->buildFilteredQuery($widget['module_id'], $filters);

        $sql = "SELECT {$fn}(CAST(JSON_UNQUOTE(JSON_EXTRACT(r.data, '$.{$slug}')) AS DECIMAL(20,4))) as result
                FROM records r {$filterSql}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $val = $stmt->fetchColumn();

        return ['value' => round((float)$val, 2), 'type' => strtolower($fn)];
    }

    /**
     * GROUP BY a field value stored in records.data JSON column.
     * Used for both bar and pie charts.
     */
    private function computeGrouped(array $widget, array $filters): array
    {
        if (!$widget['field_id']) return ['labels' => [], 'data' => [], 'type' => $widget['widget_type']];

        $slug = $this->getFieldSlug((int)$widget['field_id']);
        if (!$slug) return ['labels' => [], 'data' => [], 'type' => $widget['widget_type']];

        [$filterSql, $params] = $this->buildFilteredQuery($widget['module_id'], $filters);

        $sql = "SELECT JSON_UNQUOTE(JSON_EXTRACT(r.data, '$.{$slug}')) as label, COUNT(*) as count
                FROM records r {$filterSql}
                AND JSON_EXTRACT(r.data, '$.{$slug}') IS NOT NULL
                GROUP BY label
                ORDER BY count DESC
                LIMIT 20";

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
     * Group by Created Date for Line Charts.
     */
    private function computeTrend(array $widget, array $filters): array
    {
        [$filterSql, $params] = $this->buildFilteredQuery($widget['module_id'], $filters);
        
        $sql = "SELECT DATE(created_at) as day, COUNT(*) as count
                FROM records r {$filterSql}
                GROUP BY day
                ORDER BY day ASC
                LIMIT 30";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return [
            'labels' => array_column($rows, 'day'),
            'data'   => array_map(fn($r) => (int)$r['count'], $rows),
            'type'   => 'trend_chart',
        ];
    }

    /**
     * Compute Progress towards a goal.
     */
    private function computeProgress(array $widget, array $filters): array
    {
        $current = $this->computeCount($widget, $filters)['value'];
        $goal    = (int)($widget['filters']['goal'] ?? 100);
        
        return [
            'value'   => $current,
            'goal'    => $goal,
            'percent' => $goal > 0 ? min(100, round(($current / $goal) * 100)) : 0,
            'type'    => 'progress_bar',
        ];
    }

    /**
     * Top 5 records by a specific numeric field.
     */
    private function computeTopList(array $widget, array $filters): array
    {
        if (!$widget['field_id']) return ['items' => [], 'type' => 'top_list'];
        $slug = $this->getFieldSlug((int)$widget['field_id']);
        if (!$slug) return ['items' => [], 'type' => 'top_list'];

        [$filterSql, $params] = $this->buildFilteredQuery($widget['module_id'], $filters);
        
        $sql = "SELECT JSON_UNQUOTE(JSON_EXTRACT(r.data, '$.{$slug}')) as val, r.data
                FROM records r {$filterSql}
                ORDER BY CAST(val AS DECIMAL(20,4)) DESC
                LIMIT 5";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $items = [];
        foreach ($rows as $row) {
            $data = json_decode($row['data'], true);
            $items[] = [
                'label' => reset($data), // First field as label
                'value' => $row['val']
            ];
        }

        return ['items' => $items, 'type' => 'top_list'];
    }

    /**
     * Get a field's slug by its ID.
     */
    private function getFieldSlug(int $fieldId): ?string
    {
        $stmt = $this->db->prepare('SELECT slug FROM fields WHERE id = ?');
        $stmt->execute([$fieldId]);
        $row = $stmt->fetch();
        return $row ? $row['slug'] : null;
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
            if (!is_array($filter)) continue;
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
