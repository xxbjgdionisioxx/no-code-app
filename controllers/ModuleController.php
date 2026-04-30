<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Middleware;
use Engine\ModuleEngine;
use Engine\AppEngine;

class ModuleController extends Controller
{
    private ModuleEngine $moduleEngine;
    private AppEngine    $appEngine;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $this->moduleEngine = new ModuleEngine($db);
        $this->appEngine    = new AppEngine($db);
    }

    public function index(Request $req, array $params): void
    {
        $user  = Middleware::auth();
        $app   = $this->resolveApp($params['appId'] ?? 0);
        $mods  = $this->moduleEngine->listModules($app['id']);
        $this->view('apps.show', ['title' => $app['name'], 'app' => $app, 'modules' => $mods, 'user' => $user]);
    }

    public function create(Request $req, array $params): void
    {
        Middleware::auth();
        $app = $this->resolveApp($params['appId'] ?? 0);
        $this->view('builder.module_builder', ['title' => 'New Module', 'app' => $app, 'module' => null]);
    }

    public function store(Request $req, array $params): void
    {
        Middleware::auth();
        Middleware::csrf();

        $app  = $this->resolveApp($params['appId'] ?? 0);
        $name = strip_tags(trim($req->post('name', '')));

        if (mb_strlen($name) < 2) {
            $this->flashError('Module name must be at least 2 characters.');
            $this->redirect("/apps/{$app['id']}/modules/create");
        }

        $moduleId = $this->moduleEngine->createModule($app['id'], [
            'name'        => $name,
            'description' => strip_tags(trim($req->post('description', ''))),
            'icon'        => $req->post('icon', 'bi-table'),
            'sort_order'  => (int)$req->post('sort_order', 0),
        ]);

        $this->flashSuccess("Module '{$name}' created.");
        $this->redirect("/apps/{$app['id']}/modules/{$moduleId}/builder");
    }

    public function builder(Request $req, array $params): void
    {
        Middleware::auth();
        $app    = $this->resolveApp($params['appId'] ?? 0);
        $module = $this->resolveModule($params['id'] ?? 0);
        $schema = $this->moduleEngine->getSchema($module['id']);

        $this->view('builder.module_builder', [
            'title'  => "Builder — {$module['name']}",
            'app'    => $app,
            'module' => $module,
            'schema' => $schema,
        ], 'builder');
    }

    public function edit(Request $req, array $params): void
    {
        Middleware::auth();
        $app    = $this->resolveApp($params['appId'] ?? 0);
        $module = $this->resolveModule($params['id'] ?? 0);
        $this->view('builder.module_builder', ['title' => 'Edit Module', 'app' => $app, 'module' => $module]);
    }

    public function update(Request $req, array $params): void
    {
        Middleware::auth();
        Middleware::csrf();
        $app    = $this->resolveApp($params['appId'] ?? 0);
        $module = $this->resolveModule($params['id'] ?? 0);

        $this->moduleEngine->updateModule($module['id'], [
            'name'        => strip_tags(trim($req->post('name', ''))),
            'description' => strip_tags(trim($req->post('description', ''))),
            'icon'        => $req->post('icon', 'bi-table'),
            'sort_order'  => (int)$req->post('sort_order', 0),
        ]);

        $this->flashSuccess('Module updated.');
        $this->redirect("/apps/{$app['id']}/modules/{$module['id']}/builder");
    }

    public function destroy(Request $req, array $params): void
    {
        Middleware::admin();
        $app    = $this->resolveApp($params['appId'] ?? 0);
        $module = $this->resolveModule($params['id'] ?? 0);
        $this->moduleEngine->deleteModule($module['id']);
        $this->flashSuccess('Module deleted.');
        $this->redirect("/apps/{$app['id']}");
    }

    private function resolveApp(mixed $id): array
    {
        $app = $this->appEngine->getApp((int)$id);
        if (!$app) { http_response_code(404); require BASE_PATH.'/views/errors/404.php'; exit; }
        return $app;
    }

    private function resolveModule(mixed $id): array
    {
        $module = $this->moduleEngine->getModule((int)$id);
        if (!$module) { http_response_code(404); require BASE_PATH.'/views/errors/404.php'; exit; }
        return $module;
    }
}
