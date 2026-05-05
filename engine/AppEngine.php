<?php

namespace Engine;

/**
 * AppEngine — Application registration and context management.
 *
 * An "App" is the top-level container for a no-code system
 * (e.g., "Inventory System", "HR Platform").
 * This engine manages creating, retrieving, and activating apps.
 */
class AppEngine
{
    public function __construct(private \PDO $db) {}

    // ── CRUD ─────────────────────────────────────────────────

    /**
     * Create a new application.
     *
     * @param int    $ownerId User ID of the creator
     * @param array  $data    Validated app data (name, description, icon, color)
     * @return int   Newly created app ID
     */
    public function createApp(int $ownerId, array $data): int
    {
        $slug = $this->generateSlug($data['name']);

        $sql = 'INSERT INTO apps (name, slug, description, icon, color, owner_id, settings)
                VALUES (:name, :slug, :description, :icon, :color, :owner_id, :settings)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name'        => $data['name'],
            ':slug'        => $slug,
            ':description' => $data['description'] ?? null,
            ':icon'        => $data['icon']        ?? 'bi-grid',
            ':color'       => $data['color']       ?? '#6366f1',
            ':owner_id'    => $ownerId,
            ':settings'    => isset($data['settings']) ? json_encode($data['settings']) : null,
        ]);

        $appId = (int) $this->db->lastInsertId();

        // Auto-create a default "Admin" role for every new app
        $roleStmt = $this->db->prepare(
            'INSERT INTO roles (app_id, name, description, is_system) VALUES (?, ?, ?, 1)'
        );
        $roleStmt->execute([$appId, 'Admin', 'Full access to all modules']);

        return $appId;
    }

    /**
     * Retrieve a single app by its numeric ID.
     */
    public function getApp(int $appId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM apps WHERE id = ? AND is_active = 1');
        $stmt->execute([$appId]);
        $app = $stmt->fetch();
        if ($app) {
            $app['settings'] = $app['settings'] ? json_decode($app['settings'], true) : [];
        }
        return $app ?: null;
    }

    /**
     * Retrieve an app by its slug (used in URLs and API routes).
     */
    public function getAppBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM apps WHERE slug = ? AND is_active = 1');
        $stmt->execute([$slug]);
        $app = $stmt->fetch();
        if ($app) {
            $app['settings'] = $app['settings'] ? json_decode($app['settings'], true) : [];
        }
        return $app ?: null;
    }

    /**
     * List all apps accessible by a user.
     * Platform admins see all apps; regular users see apps where they have a role.
     */
    public function listApps(int $userId, bool $isAdmin = false): array
    {
        if ($isAdmin) {
            $stmt = $this->db->prepare(
                'SELECT * FROM apps WHERE is_active = 1 ORDER BY created_at DESC'
            );
            $stmt->execute();
        } else {
            // Only show apps where the user has been assigned a role
            $sql = 'SELECT DISTINCT a.*
                    FROM apps a
                    INNER JOIN user_roles ur ON ur.app_id = a.id
                    WHERE a.is_active = 1 AND ur.user_id = ?
                    ORDER BY a.created_at DESC';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
        }

        $apps = $stmt->fetchAll();
        foreach ($apps as &$app) {
            $app['settings'] = $app['settings'] ? json_decode($app['settings'], true) : [];
        }
        return $apps;
    }

    /**
     * Update app metadata.
     */
    public function updateApp(int $appId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE apps SET name = :name, description = :description,
             icon = :icon, color = :color, settings = :settings WHERE id = :id'
        );

        return $stmt->execute([
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? null,
            ':icon'        => $data['icon']         ?? 'bi-grid',
            ':color'       => $data['color']        ?? '#6366f1',
            ':settings'    => isset($data['settings']) ? json_encode($data['settings']) : null,
            ':id'          => $appId,
        ]);
    }

    /**
     * Soft-delete an app (sets is_active = 0).
     * Hard-deleting cascades to modules/fields/records via FK constraints.
     */
    public function deleteApp(int $appId): bool
    {
        $stmt = $this->db->prepare('UPDATE apps SET is_active = 0 WHERE id = ?');
        return $stmt->execute([$appId]);
    }

    /**
     * Return aggregate stats for an app (module count, record count).
     */
    public function getAppStats(int $appId): array
    {
        $moduleCount = $this->db->prepare(
            'SELECT COUNT(*) FROM modules WHERE app_id = ? AND is_active = 1'
        );
        $moduleCount->execute([$appId]);

        $recordCount = $this->db->prepare(
            'SELECT COUNT(*) FROM records WHERE app_id = ?'
        );
        $recordCount->execute([$appId]);

        return [
            'module_count' => (int) $moduleCount->fetchColumn(),
            'record_count' => (int) $recordCount->fetchColumn(),
        ];
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Generate a unique slug for an app name.
     * Appends a numeric suffix if the base slug is taken.
     */
    private function generateSlug(string $name): string
    {
        $base = strtolower(trim($name));
        $base = preg_replace('/[^a-z0-9\s-]/', '', $base);
        $base = preg_replace('/[\s-]+/', '-', $base);
        $base = trim($base, '-');

        $slug  = $base;
        $i     = 1;
        $check = $this->db->prepare('SELECT id FROM apps WHERE slug = ?');

        while (true) {
            $check->execute([$slug]);
            if (!$check->fetch()) {
                break;  // Slug is available
            }
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
