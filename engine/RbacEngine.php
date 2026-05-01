<?php

namespace Engine;

/**
 * RbacEngine — Role-Based Access Control enforcement.
 *
 * Enforces module-level CRUD permissions per role.
 * Platform admins (is_admin=1) bypass all RBAC checks.
 *
 * Permission matrix: can_view | can_create | can_edit | can_delete
 */
class RbacEngine
{
    /** @var array<string, bool> In-process cache to avoid repeated DB hits per request */
    private array $cache = [];

    public function __construct(private \PDO $db) {}

    // ── Permission Check ─────────────────────────────────────

    /**
     * Check if a user has a specific action permission on a module.
     *
     * @param int    $userId
     * @param int    $moduleId
     * @param string $action   view | create | edit | delete
     */
    public function can(int $userId, int $moduleId, string $action): bool
    {
        // Platform admins bypass all permission checks
        if ($this->isAdmin($userId)) return true;

        $cacheKey = "{$userId}:{$moduleId}:{$action}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $column = "can_{$action}";
        $allowed = ['can_view','can_create','can_edit','can_delete'];
        if (!in_array($column, $allowed, true)) {
            return false;
        }

        // Aggregate permissions from all roles the user holds in this module's app
        $sql = "SELECT MAX(p.{$column}) as granted
                FROM permissions p
                INNER JOIN roles r ON r.id = p.role_id
                INNER JOIN user_roles ur ON ur.role_id = r.id
                INNER JOIN modules m ON m.id = p.module_id
                WHERE ur.user_id = ? AND p.module_id = ? AND ur.app_id = m.app_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $moduleId]);
        $result = (bool)$stmt->fetchColumn();

        $this->cache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Enforce a permission — redirect with 403 if denied.
     * Intended for use inside controllers as a one-liner guard.
     */
    public function enforce(int $userId, int $moduleId, string $action): void
    {
        if (!$this->can($userId, $moduleId, $action)) {
            http_response_code(403);
            require BASE_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Return the full permission set for a user on a module.
     * Useful for rendering conditional UI buttons (Edit/Delete).
     */
    public function getPermissions(int $userId, int $moduleId): array
    {
        return [
            'can_view'   => $this->can($userId, $moduleId, 'view'),
            'can_create' => $this->can($userId, $moduleId, 'create'),
            'can_edit'   => $this->can($userId, $moduleId, 'edit'),
            'can_delete' => $this->can($userId, $moduleId, 'delete'),
        ];
    }

    // ── Role Management ──────────────────────────────────────

    public function createRole(int $appId, string $name, string $description = ''): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO roles (app_id, name, description) VALUES (?, ?, ?)'
        );
        $stmt->execute([$appId, $name, $description]);
        return (int)$this->db->lastInsertId();
    }

    public function updateRole(int $roleId, string $name, string $description): bool
    {
        return $this->db->prepare('UPDATE roles SET name=?, description=? WHERE id=?')
                        ->execute([$name, $description, $roleId]);
    }

    public function deleteRole(int $roleId): bool
    {
        // Prevent deletion of system roles
        $stmt = $this->db->prepare('SELECT is_system FROM roles WHERE id=?');
        $stmt->execute([$roleId]);
        $role = $stmt->fetch();
        if ($role && $role['is_system']) {
            throw new \RuntimeException("System roles cannot be deleted.");
        }
        return $this->db->prepare('DELETE FROM roles WHERE id=?')->execute([$roleId]);
    }

    public function listRoles(int $appId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE app_id=? ORDER BY is_system DESC, name ASC');
        $stmt->execute([$appId]);
        return $stmt->fetchAll();
    }

    public function getRole(int $roleId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE id=?');
        $stmt->execute([$roleId]);
        return $stmt->fetch() ?: null;
    }

    // ── Permission Assignment ─────────────────────────────────

    /**
     * Save (upsert) permission grants for a role on a module.
     */
    public function savePermission(int $roleId, int $moduleId, array $grants): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO permissions (role_id, module_id, can_view, can_create, can_edit, can_delete)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             can_view=VALUES(can_view), can_create=VALUES(can_create),
             can_edit=VALUES(can_edit), can_delete=VALUES(can_delete)'
        );
        $this->cache = [];  // Invalidate cache after permission change
        return $stmt->execute([
            $roleId, $moduleId,
            (int)($grants['can_view']   ?? 0),
            (int)($grants['can_create'] ?? 0),
            (int)($grants['can_edit']   ?? 0),
            (int)($grants['can_delete'] ?? 0),
        ]);
    }

    /**
     * Get all permission rows for a role across all modules in an app.
     */
    public function getRolePermissions(int $roleId, int $appId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, m.name as module_name, m.slug as module_slug
             FROM modules m
             LEFT JOIN permissions p ON p.module_id = m.id AND p.role_id = ?
             WHERE m.app_id = ? AND m.is_active = 1
             ORDER BY m.sort_order ASC'
        );
        $stmt->execute([$roleId, $appId]);
        return $stmt->fetchAll();
    }

    // ── User↔Role Assignment ──────────────────────────────────

    public function assignRole(int $userId, int $roleId, int $appId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO user_roles (user_id, role_id, app_id) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$userId, $roleId, $appId]);
    }

    public function revokeRole(int $userId, int $roleId, int $appId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM user_roles WHERE user_id=? AND role_id=? AND app_id=?'
        );
        return $stmt->execute([$userId, $roleId, $appId]);
    }

    public function getUserRoles(int $userId, int $appId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.* FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = ? AND ur.app_id = ?'
        );
        $stmt->execute([$userId, $appId]);
        return $stmt->fetchAll();
    }

    /**
     * List all app users with their assigned roles.
     */
    public function listUsersWithRoles(int $appId): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.name, u.email, u.is_admin,
                    GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ", ") as roles
             FROM users u
             LEFT JOIN user_roles ur ON ur.user_id = u.id AND ur.app_id = ?
             LEFT JOIN roles r ON r.id = ur.role_id
             GROUP BY u.id, u.name, u.email, u.is_admin
             ORDER BY u.name ASC'
        );
        $stmt->execute([$appId]);
        return $stmt->fetchAll();
    }

    // ── Helpers ──────────────────────────────────────────────

    private function isAdmin(int $userId): bool
    {
        $key = "admin:{$userId}";
        if (!isset($this->cache[$key])) {
            $stmt = $this->db->prepare('SELECT is_admin FROM users WHERE id=?');
            $stmt->execute([$userId]);
            $this->cache[$key] = (bool)$stmt->fetchColumn();
        }
        return $this->cache[$key];
    }
}
