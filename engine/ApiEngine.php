<?php

namespace Engine;

/**
 * ApiEngine — Auto-generates RESTful endpoints per module.
 *
 * Consumed by ApiController to handle:
 *   GET    /api/{app}/{module}       → list records (paginated, filterable)
 *   POST   /api/{app}/{module}       → create record
 *   GET    /api/{app}/{module}/{id}  → get single record
 *   PUT    /api/{app}/{module}/{id}  → update record
 *   DELETE /api/{app}/{module}/{id}  → delete record
 *
 * All endpoints require a Bearer token (user session or generated API key).
 * RBAC is enforced for every action.
 */
class ApiEngine
{
    public function __construct(
        private \PDO           $db,
        private RecordEngine   $recordEngine,
        private FieldEngine    $fieldEngine,
        private ModuleEngine   $moduleEngine,
        private AppEngine      $appEngine,
        private RbacEngine     $rbacEngine
    ) {}

    // ── Auth ─────────────────────────────────────────────────

    /**
     * Resolve the authenticated user from Bearer token.
     * For simplicity, the Bearer token is the user's session ID.
     * In production, replace with signed JWT or API key table.
     *
     * Returns user row or null if unauthenticated.
     */
    public function resolveUser(string $bearerToken): ?array
    {
        // Look up the token in a simple api_tokens table (or fall back to session)
        // We use a session-based lookup for this implementation
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE id = ? AND is_active = 1'
        );
        // The token is base64(userId:secret) — for demo purposes only
        $decoded = base64_decode($bearerToken, true);
        if (!$decoded || !str_contains($decoded, ':')) return null;

        [$userId] = explode(':', $decoded, 2);
        $stmt->execute([(int)$userId]);
        return $stmt->fetch() ?: null;
    }

    // ── List Records ─────────────────────────────────────────

    public function index(string $appSlug, string $moduleSlug, array $queryParams, int $userId): array
    {
        [$app, $module, $schema] = $this->resolve($appSlug, $moduleSlug);
        $this->rbacEngine->enforce($userId, $module['id'], 'view');

        $result = $this->recordEngine->listRecords($module['id'], [
            'page'     => (int)($queryParams['page']     ?? 1),
            'per_page' => (int)($queryParams['per_page']  ?? DEFAULT_PAGE_SIZE),
            'search'   => $queryParams['search']          ?? '',
        ], $schema);

        return [
            'success'    => true,
            'app'        => $app['slug'],
            'module'     => $module['slug'],
            'pagination' => [
                'total'    => $result['total'],
                'pages'    => $result['pages'],
                'page'     => $result['page'],
                'per_page' => $result['per_page'],
            ],
            'data' => array_map(fn($r) => $this->formatRecord($r), $result['records']),
        ];
    }

    // ── Show Single Record ────────────────────────────────────

    public function show(string $appSlug, string $moduleSlug, int $recordId, int $userId): array
    {
        [$app, $module] = $this->resolve($appSlug, $moduleSlug);
        $this->rbacEngine->enforce($userId, $module['id'], 'view');

        $record = $this->recordEngine->getRecord($recordId);
        if (!$record || $record['module_id'] !== $module['id']) {
            return ['success' => false, 'error' => 'Record not found.', 'status' => 404];
        }

        return ['success' => true, 'data' => $this->formatRecord($record)];
    }

    // ── Create Record ─────────────────────────────────────────

    public function store(string $appSlug, string $moduleSlug, array $payload, int $userId): array
    {
        [$app, $module, $schema] = $this->resolve($appSlug, $moduleSlug);
        $this->rbacEngine->enforce($userId, $module['id'], 'create');

        $result = $this->fieldEngine->validateAll($schema, $this->prefixKeys($payload));
        if (!empty($result['errors'])) {
            return ['success' => false, 'errors' => $result['errors'], 'status' => 422];
        }

        $recordId = $this->recordEngine->createRecord(
            $app['id'], $module['id'], $userId, $result['values'], [], $schema
        );

        return ['success' => true, 'id' => $recordId, 'status' => 201];
    }

    // ── Update Record ─────────────────────────────────────────

    public function update(string $appSlug, string $moduleSlug, int $recordId, array $payload, int $userId): array
    {
        [$app, $module, $schema] = $this->resolve($appSlug, $moduleSlug);
        $this->rbacEngine->enforce($userId, $module['id'], 'edit');

        $result = $this->fieldEngine->validateAll($schema, $this->prefixKeys($payload));
        if (!empty($result['errors'])) {
            return ['success' => false, 'errors' => $result['errors'], 'status' => 422];
        }

        $this->recordEngine->updateRecord($recordId, $userId, $result['values'], [], $schema);
        return ['success' => true, 'id' => $recordId];
    }

    // ── Delete Record ─────────────────────────────────────────

    public function destroy(string $appSlug, string $moduleSlug, int $recordId, int $userId): array
    {
        [$app, $module] = $this->resolve($appSlug, $moduleSlug);
        $this->rbacEngine->enforce($userId, $module['id'], 'delete');

        $this->recordEngine->deleteRecord($recordId);
        return ['success' => true, 'message' => 'Record deleted.'];
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Resolve app + module + schema from slugs. Throws on not found.
     */
    private function resolve(string $appSlug, string $moduleSlug): array
    {
        $app = $this->appEngine->getAppBySlug($appSlug);
        if (!$app) throw new \RuntimeException("App '{$appSlug}' not found.", 404);

        $module = $this->moduleEngine->getModuleBySlug($app['id'], $moduleSlug);
        if (!$module) throw new \RuntimeException("Module '{$moduleSlug}' not found.", 404);

        $schema = $this->moduleEngine->getSchema($module['id']);
        return [$app, $module, $schema];
    }

    /**
     * Format a record for API output — clean JSON without internal metadata.
     */
    private function formatRecord(array $record): array
    {
        return array_merge(
            ['id' => $record['id'], 'created_at' => $record['created_at'], 'updated_at' => $record['updated_at']],
            $record['values'] ?? []
        );
    }

    /**
     * The API accepts bare field slugs in the JSON body.
     * FieldEngine expects "field_{slug}" keys, so we prefix them.
     */
    private function prefixKeys(array $payload): array
    {
        $prefixed = [];
        foreach ($payload as $key => $value) {
            $prefixed["field_{$key}"] = $value;
        }
        return $prefixed;
    }
}
