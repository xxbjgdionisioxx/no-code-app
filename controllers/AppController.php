<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Middleware;
use Core\CSRF;
use Engine\AppEngine;

class AppController extends Controller
{
    private AppEngine $appEngine;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $this->appEngine = new AppEngine($db);
    }

    public function index(Request $req): void
    {
        $user = Middleware::auth(false);
        $apps = $this->appEngine->listApps($user['id'], $user['is_admin']);
        $this->view('apps.index', ['title' => 'My Apps', 'apps' => $apps, 'user' => $user]);
    }

    public function create(Request $req): void
    {
        Middleware::auth();
        $this->view('apps.create', ['title' => 'Create New App']);
    }

    public function store(Request $req): void
    {
        $user = Middleware::auth();
        Middleware::csrf();

        $name = strip_tags(trim($req->post('name', '')));
        if (mb_strlen($name) < 2) {
            $this->flashError('App name must be at least 2 characters.');
            $this->redirect('/apps/create');
        }

        $appId = $this->appEngine->createApp($user['id'], [
            'name'        => $name,
            'description' => strip_tags(trim($req->post('description', ''))),
            'icon'        => $req->post('icon', 'bi-grid'),
            'color'       => $req->post('color', '#6366f1'),
        ]);

        $this->flashSuccess("App '{$name}' created successfully!");
        $this->redirect("/apps/{$appId}");
    }

    public function show(Request $req, array $params): void
    {
        $user  = Middleware::auth();
        $appId = (int)($params['id'] ?? 0);
        $app   = $this->appEngine->getApp($appId);
        if (!$app) { http_response_code(404); require BASE_PATH.'/views/errors/404.php'; return; }

        $stats   = $this->appEngine->getAppStats($appId);
        $modules = (new \Engine\ModuleEngine($this->db))->listModules($appId);

        $this->view('apps.show', [
            'title'   => $app['name'],
            'app'     => $app,
            'modules' => $modules,
            'stats'   => $stats,
            'user'    => $user,
        ]);
    }

    public function edit(Request $req, array $params): void
    {
        $user  = Middleware::auth();
        $appId = (int)($params['id'] ?? 0);
        $app   = $this->appEngine->getApp($appId);
        if (!$app) { http_response_code(404); require BASE_PATH.'/views/errors/404.php'; return; }

        $this->view('apps.create', ['title' => 'Edit App', 'app' => $app, 'user' => $user]);
    }

    public function update(Request $req, array $params): void
    {
        Middleware::auth();
        Middleware::csrf();

        $appId = (int)($params['id'] ?? 0);
        $this->appEngine->updateApp($appId, [
            'name'        => strip_tags(trim($req->post('name', ''))),
            'description' => strip_tags(trim($req->post('description', ''))),
            'icon'        => $req->post('icon', 'bi-grid'),
            'color'       => $req->post('color', '#6366f1'),
        ]);

        $this->flashSuccess('App updated.');
        $this->redirect("/apps/{$appId}");
    }

    public function destroy(Request $req, array $params): void
    {
        Middleware::admin();
        $appId = (int)($params['id'] ?? 0);
        $this->appEngine->deleteApp($appId);
        $this->flashSuccess('App deleted.');
        $this->redirect('/apps');
    }
}
