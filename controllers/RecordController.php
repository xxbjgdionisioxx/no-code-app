<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Middleware;
use Engine\AppEngine;
use Engine\ModuleEngine;
use Engine\FieldEngine;
use Engine\RecordEngine;
use Engine\RbacEngine;
use Engine\WorkflowEngine;
use Plugins\PluginManager;

/**
 * RecordController — Dynamic CRUD UI for any module's records.
 *
 * All routes are parameterized by appId + moduleSlug so the same
 * controller serves every module in every app.
 */
class RecordController extends Controller
{
    private AppEngine      $appEngine;
    private ModuleEngine   $moduleEngine;
    private FieldEngine    $fieldEngine;
    private RecordEngine   $recordEngine;
    private RbacEngine     $rbacEngine;
    private WorkflowEngine $workflowEngine;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $pm                  = new PluginManager();  // Minimal instance for record engine
        $this->appEngine     = new AppEngine($db);
        $this->moduleEngine  = new ModuleEngine($db);
        $this->fieldEngine   = new FieldEngine($db);
        $this->recordEngine  = new RecordEngine($db, $this->fieldEngine, $pm);
        $this->rbacEngine    = new RbacEngine($db);
        $this->workflowEngine= new WorkflowEngine($db);
    }

    // ── List ─────────────────────────────────────────────────

    public function index(Request $req, array $params): void
    {
        $user   = Middleware::auth();
        [$app, $module, $schema] = $this->resolveContext($params);
        $this->rbacEngine->enforce($user['id'], $module['id'], 'view');

        $result = $this->recordEngine->listRecords($module['id'], [
            'page'     => (int)$req->query('page', 1),
            'per_page' => (int)$req->query('per_page', DEFAULT_PAGE_SIZE),
            'search'   => $req->query('search', ''),
            'sort_dir' => $req->query('sort_dir', 'DESC'),
        ], $schema);

        $listFields = array_filter($schema['fields'], fn($f) => $f['show_in_list']);
        $perms      = $this->rbacEngine->getPermissions($user['id'], $module['id']);

        $this->view('records.index', [
            'title'       => $module['name'],
            'app'         => $app,
            'module'      => $module,
            'schema'      => $schema,
            'listFields'  => array_values($listFields),
            'result'      => $result,
            'perms'       => $perms,
            'user'        => $user,
            'fieldEngine' => $this->fieldEngine,
            'search'      => $req->query('search', ''),
        ]);
    }

    // ── Create Form ───────────────────────────────────────────

    public function create(Request $req, array $params): void
    {
        $user = Middleware::auth();
        [$app, $module, $schema] = $this->resolveContext($params);
        $this->rbacEngine->enforce($user['id'], $module['id'], 'create');

        $this->view('records.create', [
            'title'       => "New {$module['name']}",
            'app'         => $app,
            'module'      => $module,
            'schema'      => $schema,
            'fieldEngine' => $this->fieldEngine,
            'errors'      => [],
            'oldValues'   => [],
            'user'        => $user,
        ]);
    }

    // ── Store ─────────────────────────────────────────────────

    public function store(Request $req, array $params): void
    {
        $user = Middleware::auth();
        Middleware::csrf();
        [$app, $module, $schema] = $this->resolveContext($params);
        $this->rbacEngine->enforce($user['id'], $module['id'], 'create');

        $result = $this->fieldEngine->validateAll($schema, $req->postAll());

        if (!empty($result['errors'])) {
            $this->view('records.create', [
                'title'       => "New {$module['name']}",
                'app'         => $app,
                'module'      => $module,
                'schema'      => $schema,
                'fieldEngine' => $this->fieldEngine,
                'errors'      => $result['errors'],
                'oldValues'   => $req->postAll(),
                'user'        => $user,
            ]);
            return;
        }

        try {
            $recordId = $this->recordEngine->createRecord(
                $app['id'], $module['id'], $user['id'],
                $result['values'], $req->files(), $schema
            );
            // Evaluate workflows after create
            $this->workflowEngine->evaluate($module['id'], 'create', $result['values'], $recordId);
        } catch (\RuntimeException $e) {
            $this->view('records.create', [
                'title'       => "New {$module['name']}",
                'app'         => $app, 'module' => $module, 'schema' => $schema,
                'fieldEngine' => $this->fieldEngine,
                'errors'      => ['_global' => [$e->getMessage()]],
                'oldValues'   => $req->postAll(),
                'user'        => $user,
            ]);
            return;
        }

        $this->flashSuccess('Record created successfully.');
        $this->redirect("/apps/{$app['id']}/{$module['slug']}");
    }

    // ── Show ─────────────────────────────────────────────────

    public function show(Request $req, array $params): void
    {
        $user = Middleware::auth();
        [$app, $module, $schema] = $this->resolveContext($params);
        $this->rbacEngine->enforce($user['id'], $module['id'], 'view');

        $record = $this->recordEngine->getRecord((int)($params['id'] ?? 0));
        if (!$record) { http_response_code(404); require BASE_PATH.'/views/errors/404.php'; return; }

        $perms = $this->rbacEngine->getPermissions($user['id'], $module['id']);

        $this->view('records.show', [
            'title'       => "{$module['name']} — Record #{$record['id']}",
            'app'         => $app, 'module' => $module, 'schema' => $schema,
            'record'      => $record,
            'perms'       => $perms,
            'fieldEngine' => $this->fieldEngine,
            'user'        => $user,
        ]);
    }

    // ── Edit Form ─────────────────────────────────────────────

    public function edit(Request $req, array $params): void
    {
        $user = Middleware::auth();
        [$app, $module, $schema] = $this->resolveContext($params);
        $this->rbacEngine->enforce($user['id'], $module['id'], 'edit');

        $record = $this->recordEngine->getRecord((int)($params['id'] ?? 0));
        if (!$record) { http_response_code(404); require BASE_PATH.'/views/errors/404.php'; return; }

        $this->view('records.edit', [
            'title'       => "Edit {$module['name']} #{$record['id']}",
            'app'         => $app, 'module' => $module, 'schema' => $schema,
            'record'      => $record,
            'fieldEngine' => $this->fieldEngine,
            'errors'      => [],
            'user'        => $user,
        ]);
    }

    // ── Update ────────────────────────────────────────────────

    public function update(Request $req, array $params): void
    {
        $user = Middleware::auth();
        Middleware::csrf();
        [$app, $module, $schema] = $this->resolveContext($params);
        $this->rbacEngine->enforce($user['id'], $module['id'], 'edit');

        $recordId = (int)($params['id'] ?? 0);
        $record   = $this->recordEngine->getRecord($recordId);
        if (!$record) { http_response_code(404); require BASE_PATH.'/views/errors/404.php'; return; }

        $result = $this->fieldEngine->validateAll($schema, $req->postAll());

        if (!empty($result['errors'])) {
            $this->view('records.edit', [
                'title'       => "Edit {$module['name']} #{$recordId}",
                'app'         => $app, 'module' => $module, 'schema' => $schema,
                'record'      => $record,
                'fieldEngine' => $this->fieldEngine,
                'errors'      => $result['errors'],
                'user'        => $user,
            ]);
            return;
        }

        try {
            $this->recordEngine->updateRecord($recordId, $user['id'], $result['values'], $req->files(), $schema);
            $this->workflowEngine->evaluate($module['id'], 'update', $result['values'], $recordId);
        } catch (\RuntimeException $e) {
            $this->view('records.edit', [
                'title'       => "Edit {$module['name']} #{$recordId}",
                'app'         => $app, 'module' => $module, 'schema' => $schema,
                'record'      => $record,
                'fieldEngine' => $this->fieldEngine,
                'errors'      => ['_global' => [$e->getMessage()]],
                'user'        => $user,
            ]);
            return;
        }

        $this->flashSuccess('Record updated.');
        $this->redirect("/apps/{$app['id']}/{$module['slug']}/{$recordId}");
    }

    // ── Delete ────────────────────────────────────────────────

    public function destroy(Request $req, array $params): void
    {
        $user = Middleware::auth();
        [$app, $module] = $this->resolveContext($params);
        $this->rbacEngine->enforce($user['id'], $module['id'], 'delete');

        $recordId = (int)($params['id'] ?? 0);
        $record   = $this->recordEngine->getRecord($recordId);
        if ($record) {
            $this->workflowEngine->evaluate($module['id'], 'delete', $record['values'], $recordId);
        }

        $this->recordEngine->deleteRecord($recordId);

        $this->flashSuccess('Record deleted.');
        $this->redirect("/apps/{$app['id']}/{$module['slug']}");
    }

    // ── Export ────────────────────────────────────────────────
    public function export(Request $req, array $params): void
    {
        $user = Middleware::auth();
        [$app, $module, $schema] = $this->resolveContext($params);
        $this->rbacEngine->enforce($user['id'], $module['id'], 'view');

        $format = $req->query('format', 'csv');

        // Fetch all records (up to 50,000 for export performance)
        $result = $this->recordEngine->listRecords($module['id'], [
            'page'     => 1,
            'per_page' => 50000,
            'search'   => $req->query('search', ''),
            'sort_dir' => 'DESC',
        ], $schema);

        $filename = $module['slug'] . '_export_' . date('Ymd_His');

        if ($format === 'json') {
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.json"');
            echo json_encode($result['records'], JSON_PRETTY_PRINT);
            exit;
        }

        $ext = ($format === 'txt') ? 'txt' : 'csv';
        if ($format === 'txt') {
            header('Content-Type: text/plain; charset=utf-8');
        } elseif ($format === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        } else {
            header('Content-Type: text/csv; charset=utf-8');
        }
        header('Content-Disposition: attachment; filename="' . $filename . '.' . $ext . '"');

        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 support
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        $delimiter = ($format === 'txt') ? "\t" : ",";

        // Header Row
        $header = ['Record ID'];
        foreach ($schema['fields'] as $field) {
            $header[] = $field['name'];
        }
        $header[] = 'Created At';
        fputcsv($output, $header, $delimiter);

        // Data Rows
        foreach ($result['records'] as $record) {
            $row = [$record['id']];
            foreach ($schema['fields'] as $field) {
                $val = $record['values'][$field['slug']] ?? '';
                if (is_array($val)) $val = json_encode($val);
                $row[] = $val;
            }
            $row[] = $record['created_at'];
            fputcsv($output, $row, $delimiter);
        }

        fclose($output);
        exit;
    }

    // ── Helper ───────────────────────────────────────────────

    private function resolveContext(array $params): array
    {
        $appId      = (int)($params['appId']      ?? 0);
        $moduleSlug =       $params['moduleSlug']  ?? '';

        $app = $this->appEngine->getApp($appId);
        if (!$app) { http_response_code(404); require BASE_PATH.'/views/errors/404.php'; exit; }

        $module = $this->moduleEngine->getModuleBySlug($app['id'], $moduleSlug);
        if (!$module) { http_response_code(404); require BASE_PATH.'/views/errors/404.php'; exit; }

        $schema = $this->moduleEngine->getSchema($module['id']);
        return [$app, $module, $schema];
    }
}
