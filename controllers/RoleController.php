<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Middleware;
use Engine\AppEngine;
use Engine\ModuleEngine;
use Engine\RbacEngine;

class RoleController extends Controller
{
    private AppEngine    $appEngine;
    private ModuleEngine $moduleEngine;
    private RbacEngine   $rbacEngine;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $this->appEngine   = new AppEngine($db);
        $this->moduleEngine= new ModuleEngine($db);
        $this->rbacEngine  = new RbacEngine($db);
    }

    public function index(Request $req, array $params): void
    {
        Middleware::admin();
        $app   = $this->resolveApp($params['appId'] ?? 0);
        $roles = $this->rbacEngine->listRoles($app['id']);
        $this->view('roles.index', ['title' => 'Roles', 'app' => $app, 'roles' => $roles]);
    }

    public function create(Request $req, array $params): void
    {
        Middleware::admin();
        $app = $this->resolveApp($params['appId'] ?? 0);
        $this->view('roles.index', ['title' => 'Create Role', 'app' => $app, 'roles' => [], 'creating' => true]);
    }

    public function store(Request $req, array $params): void
    {
        Middleware::admin();
        Middleware::csrf();
        $app = $this->resolveApp($params['appId'] ?? 0);
        $name = strip_tags(trim($req->post('name', '')));
        if (!$name) { $this->flashError('Role name required.'); $this->redirect("/apps/{$app['id']}/roles"); }

        $roleId = $this->rbacEngine->createRole($app['id'], $name, strip_tags($req->post('description', '')));
        $this->flashSuccess("Role '{$name}' created.");
        $this->redirect("/apps/{$app['id']}/roles/{$roleId}/permissions");
    }

    public function edit(Request $req, array $params): void
    {
        Middleware::admin();
        $app  = $this->resolveApp($params['appId'] ?? 0);
        $role = $this->rbacEngine->getRole((int)$params['id']);
        $this->view('roles.index', ['title' => 'Edit Role', 'app' => $app, 'roles' => [], 'role' => $role, 'editing' => true]);
    }

    public function update(Request $req, array $params): void
    {
        Middleware::admin();
        Middleware::csrf();
        $app    = $this->resolveApp($params['appId'] ?? 0);
        $roleId = (int)$params['id'];
        $this->rbacEngine->updateRole($roleId, strip_tags(trim($req->post('name',''))), strip_tags($req->post('description','')));
        $this->flashSuccess('Role updated.');
        $this->redirect("/apps/{$app['id']}/roles");
    }

    public function destroy(Request $req, array $params): void
    {
        Middleware::admin();
        $app    = $this->resolveApp($params['appId'] ?? 0);
        $roleId = (int)$params['id'];
        try {
            $this->rbacEngine->deleteRole($roleId);
            $this->flashSuccess('Role deleted.');
        } catch (\RuntimeException $e) {
            $this->flashError($e->getMessage());
        }
        $this->redirect("/apps/{$app['id']}/roles");
    }

    public function permissions(Request $req, array $params): void
    {
        Middleware::admin();
        $app     = $this->resolveApp($params['appId'] ?? 0);
        $role    = $this->rbacEngine->getRole((int)$params['id']);
        $perms   = $this->rbacEngine->getRolePermissions($role['id'], $app['id']);
        $this->view('roles.assign', [
            'title'  => "Permissions — {$role['name']}",
            'app'    => $app,
            'role'   => $role,
            'perms'  => $perms,
        ]);
    }

    public function savePermissions(Request $req, array $params): void
    {
        Middleware::admin();
        Middleware::csrf();
        $app    = $this->resolveApp($params['appId'] ?? 0);
        $roleId = (int)$params['id'];
        $grants = $req->post('grants', []);   // ['module_id' => ['can_view'=>1, ...]]

        foreach ($grants as $moduleId => $g) {
            $this->rbacEngine->savePermission($roleId, (int)$moduleId, [
                'can_view'   => isset($g['can_view'])   ? 1 : 0,
                'can_create' => isset($g['can_create']) ? 1 : 0,
                'can_edit'   => isset($g['can_edit'])   ? 1 : 0,
                'can_delete' => isset($g['can_delete']) ? 1 : 0,
            ]);
        }

        $this->flashSuccess('Permissions saved.');
        $this->redirect("/apps/{$app['id']}/roles");
    }

    public function users(Request $req, array $params): void
    {
        Middleware::admin();
        $app   = $this->resolveApp($params['appId'] ?? 0);
        $users = $this->rbacEngine->listUsersWithRoles($app['id']);
        $roles = $this->rbacEngine->listRoles($app['id']);
        $this->view('roles.assign', ['title' => 'User Management', 'app' => $app, 'users' => $users, 'roles' => $roles, 'showUsers' => true]);
    }

    public function storeUser(Request $req, array $params): void
    {
        Middleware::admin();
        Middleware::csrf();
        $app = $this->resolveApp($params['appId'] ?? 0);

        $name     = strip_tags(trim($req->post('name', '')));
        $email    = strip_tags(trim($req->post('email', '')));
        $password = $req->post('password', 'password123');
        $isAdmin  = (int)(bool)$req->post('is_admin', 0);

        if (!$name || !$email) {
            $this->flashError('Name and Email are required.');
            $this->redirect("/apps/{$app['id']}/users");
        }

        // Check if exists
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $this->flashError('Email already registered.');
            $this->redirect("/apps/{$app['id']}/users");
        }

        $hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password, is_admin, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())');
        $stmt->execute([$name, $email, $hash, $isAdmin]);

        $this->flashSuccess("User '{$name}' created.");
        $this->redirect("/apps/{$app['id']}/users");
    }

    public function assignRole(Request $req, array $params): void
    {
        Middleware::admin();
        Middleware::csrf();
        $app    = $this->resolveApp($params['appId'] ?? 0);
        $userId = (int)$params['userId'];
        $roleId = (int)$req->post('role_id');
        $action = $req->post('action', 'assign');

        if ($action === 'revoke') {
            $this->rbacEngine->revokeRole($userId, $roleId, $app['id']);
        } else {
            $this->rbacEngine->assignRole($userId, $roleId, $app['id']);
        }

        $this->flashSuccess('Role assignment updated.');
        $this->redirect("/apps/{$app['id']}/users");
    }

    private function resolveApp(mixed $id): array
    {
        $app = $this->appEngine->getApp((int)$id);
        if (!$app) { http_response_code(404); require BASE_PATH.'/views/errors/404.php'; exit; }
        return $app;
    }
}
