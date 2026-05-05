<?php

namespace Engine;

/**
 * ModuleEngine — Module creation, retrieval, and schema management.
 *
 * A Module represents a data entity within an App
 * (e.g., "Products", "Employees", "Appointments").
 * Each module has a set of fields and a collection of records.
 */
class ModuleEngine
{
    public function __construct(private \PDO $db) {}

    // ── CRUD ─────────────────────────────────────────────────

    /**
     * Create a new module within an app.
     *
     * @return int Newly created module ID
     */
    public function createModule(int $appId, array $data): int
    {
        $slug = $this->generateModuleSlug($appId, $data['name']);

        $stmt = $this->db->prepare(
            'INSERT INTO modules (app_id, name, slug, description, icon, sort_order, settings)
             VALUES (:app_id, :name, :slug, :description, :icon, :sort_order, :settings)'
        );

        $stmt->execute([
            ':app_id'      => $appId,
            ':name'        => $data['name'],
            ':slug'        => $slug,
            ':description' => $data['description'] ?? null,
            ':icon'        => $data['icon']        ?? 'bi-table',
            ':sort_order'  => $data['sort_order']  ?? 0,
            ':settings'    => isset($data['settings']) ? json_encode($data['settings']) : null,
        ]);

        $moduleId = (int) $this->db->lastInsertId();

        // Auto-grant Admin role full CRUD on this new module
        $this->autoGrantAdminPermissions($appId, $moduleId);

        return $moduleId;
    }

    /**
     * Retrieve a single module by ID.
     */
    public function getModule(int $moduleId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM modules WHERE id = ? AND is_active = 1');
        $stmt->execute([$moduleId]);
        $module = $stmt->fetch();
        if ($module) {
            $module['settings'] = $module['settings'] ? json_decode($module['settings'], true) : [];
        }
        return $module ?: null;
    }

    /**
     * Retrieve a module by its slug within a specific app.
     * Used by the dynamic router to map URL segments to modules.
     */
    public function getModuleBySlug(int $appId, string $slug): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM modules WHERE app_id = ? AND slug = ? AND is_active = 1'
        );
        $stmt->execute([$appId, $slug]);
        $module = $stmt->fetch();
        if ($module) {
            $module['settings'] = $module['settings'] ? json_decode($module['settings'], true) : [];
        }
        return $module ?: null;
    }

    /**
     * List all active modules for an app, ordered by sort_order.
     */
    public function listModules(int $appId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM modules WHERE app_id = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$appId]);
        $modules = $stmt->fetchAll();
        foreach ($modules as &$m) {
            $m['settings'] = $m['settings'] ? json_decode($m['settings'], true) : [];
        }
        return $modules;
    }

    /**
     * Update module metadata.
     */
    public function updateModule(int $moduleId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE modules SET name = :name, description = :description,
             icon = :icon, sort_order = :sort_order, settings = :settings WHERE id = :id'
        );

        return $stmt->execute([
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? null,
            ':icon'        => $data['icon']         ?? 'bi-table',
            ':sort_order'  => $data['sort_order']   ?? 0,
            ':settings'    => isset($data['settings']) ? json_encode($data['settings']) : null,
            ':id'          => $moduleId,
        ]);
    }

    /**
     * Soft-delete a module. Cascades to fields and records via FK.
     */
    public function deleteModule(int $moduleId): bool
    {
        $stmt = $this->db->prepare('UPDATE modules SET is_active = 0 WHERE id = ?');
        return $stmt->execute([$moduleId]);
    }

    // ── Schema Introspection ─────────────────────────────────

    /**
     * Return the full schema of a module: module metadata + all field definitions.
     * This is the central method called by RecordEngine and FieldEngine renderers.
     */
    public function getSchema(int $moduleId): array
    {
        $module = $this->getModule($moduleId);
        if (!$module) {
            throw new \RuntimeException("Module [{$moduleId}] not found.");
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM fields WHERE module_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$moduleId]);
        $fields = $stmt->fetchAll();

        // Decode JSON columns for each field
        foreach ($fields as &$field) {
            $field['validation'] = $field['validation'] ? json_decode($field['validation'], true) : [];
            $field['options']    = $field['options']    ? json_decode($field['options'], true)    : [];
        }
        unset($field);

        $module['fields'] = $fields;
        return $module;
    }

    // ── Sorting ──────────────────────────────────────────────

    /**
     * Reorder modules within an app by accepting an ordered list of IDs.
     *
     * @param array $orderedIds  Module IDs in the desired display order
     */
    public function reorderModules(array $orderedIds): void
    {
        $stmt = $this->db->prepare('UPDATE modules SET sort_order = ? WHERE id = ?');
        foreach ($orderedIds as $position => $id) {
            $stmt->execute([$position, (int) $id]);
        }
    }

    // ── Helpers ──────────────────────────────────────────────

    private function generateModuleSlug(int $appId, string $name): string
    {
        $base = strtolower(trim($name));
        $base = preg_replace('/[^a-z0-9\s_-]/', '', $base);
        $base = preg_replace('/[\s-]+/', '_', $base);
        $base = trim($base, '_');

        $slug  = $base;
        $i     = 1;
        $check = $this->db->prepare('SELECT id FROM modules WHERE app_id = ? AND slug = ?');

        while (true) {
            $check->execute([$appId, $slug]);
            if (!$check->fetch()) {
                break;
            }
            $slug = $base . '_' . $i++;
        }

        return $slug;
    }

    /**
     * When a new module is created, automatically grant the app's Admin role
     * full CRUD permissions on it so admins always have access.
     */
    private function autoGrantAdminPermissions(int $appId, int $moduleId): void
    {
        // Find the system Admin role for this app
        $roleStmt = $this->db->prepare(
            'SELECT id FROM roles WHERE app_id = ? AND is_system = 1 LIMIT 1'
        );
        $roleStmt->execute([$appId]);
        $role = $roleStmt->fetch();

        if (!$role) {
            return;
        }

        $permStmt = $this->db->prepare(
            'INSERT INTO permissions (role_id, module_id, can_view, can_create, can_edit, can_delete)
             VALUES (?, ?, 1, 1, 1, 1)
             ON DUPLICATE KEY UPDATE can_view=1, can_create=1, can_edit=1, can_delete=1'
        );
        $permStmt->execute([$role['id'], $moduleId]);
    }
}
