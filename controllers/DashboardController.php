<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Middleware;
use Engine\AppEngine;
use Engine\ModuleEngine;
use Engine\DashboardEngine;
use Engine\RbacEngine;

class DashboardController extends Controller
{
    private AppEngine       $appEngine;
    private ModuleEngine    $moduleEngine;
    private DashboardEngine $dashEngine;
    private RbacEngine      $rbacEngine;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $this->appEngine   = new AppEngine($db);
        $this->moduleEngine= new ModuleEngine($db);
        $this->dashEngine  = new DashboardEngine($db);
        $this->rbacEngine  = new RbacEngine($db);
    }

    public function index(Request $req, array $params): void
    {
        $user   = Middleware::auth();
        $app    = $this->resolveApp($params['appId'] ?? 0);
        $widgets= $this->dashEngine->listWidgets($app['id'], $user['id']);
        $modules= $this->moduleEngine->listModules($app['id']);

        $this->view('dashboard.index', [
            'title'   => "Dashboard — {$app['name']}",
            'app'     => $app,
            'widgets' => $widgets,
            'modules' => $modules,
            'user'    => $user,
        ]);
    }

    public function createWidget(Request $req, array $params): void
    {
        $user    = Middleware::auth();
        $app     = $this->resolveApp($params['appId'] ?? 0);
        $modules = $this->moduleEngine->listModules($app['id']);

        // Build field list per module for the widget form
        $fieldsByModule = [];
        foreach ($modules as $m) {
            $schema = $this->moduleEngine->getSchema($m['id']);
            $fieldsByModule[$m['id']] = array_filter(
                $schema['fields'],
                fn($f) => in_array($f['field_type'], ['number', 'dropdown'], true)
            );
        }

        $this->view('dashboard.widget_builder', [
            'title'          => 'Add Widget',
            'app'            => $app,
            'modules'        => $modules,
            'fieldsByModule' => $fieldsByModule,
            'user'           => $user,
        ]);
    }

    public function storeWidget(Request $req, array $params): void
    {
        $user = Middleware::auth();
        Middleware::csrf();
        $app  = $this->resolveApp($params['appId'] ?? 0);

        $this->dashEngine->createWidget($app['id'], $user['id'], [
            'title'       => strip_tags(trim($req->post('title', 'Widget'))),
            'widget_type' => $req->post('widget_type', 'count'),
            'module_id'   => (int)$req->post('module_id'),
            'field_id'    => $req->post('field_id') ? (int)$req->post('field_id') : null,
            'chart_color' => $req->post('chart_color', '#6366f1'),
            'width'       => (int)$req->post('width', 4),
        ]);

        $this->flashSuccess('Widget added.');
        $this->redirect("/apps/{$app['id']}/dashboard");
    }

    public function destroyWidget(Request $req, array $params): void
    {
        Middleware::auth();
        $app      = $this->resolveApp($params['appId'] ?? 0);
        $widgetId = (int)($params['id'] ?? 0);
        $this->dashEngine->deleteWidget($widgetId);
        $this->flashSuccess('Widget removed.');
        $this->redirect("/apps/{$app['id']}/dashboard");
    }

    /**
     * AJAX endpoint — returns widget data as JSON for Chart.js rendering.
     */
    public function widgetData(Request $req, array $params): void
    {
        Middleware::auth();
        $widgetId = (int)($params['widgetId'] ?? 0);
        $widget   = $this->dashEngine->getWidget($widgetId);

        if (!$widget) {
            $this->jsonError('Widget not found.', 404);
        }

        $data = $this->dashEngine->compute($widget);
        $this->json(['success' => true, 'widget' => $widget, 'data' => $data]);
    }

    private function resolveApp(mixed $id): array
    {
        $app = $this->appEngine->getApp((int)$id);
        if (!$app) { http_response_code(404); require BASE_PATH.'/views/errors/404.php'; exit; }
        return $app;
    }
}
