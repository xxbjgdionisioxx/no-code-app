<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Engine\ApiEngine;
use Engine\AppEngine;
use Engine\ModuleEngine;
use Engine\FieldEngine;
use Engine\RecordEngine;
use Engine\RbacEngine;
use Plugins\PluginManager;

/**
 * ApiController — REST API handler.
 * All responses are JSON. Auth via Bearer token.
 */
class ApiController extends Controller
{
    private ApiEngine $apiEngine;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $pm = new PluginManager();
        $fe = new FieldEngine($db);
        $me = new ModuleEngine($db);
        $ae = new AppEngine($db);
        $re = new RecordEngine($db, $fe, $pm);
        $rb = new RbacEngine($db);

        $this->apiEngine = new ApiEngine($db, $re, $fe, $me, $ae, $rb);
    }

    public function index(Request $req, array $params): void
    {
        $user = $this->resolveApiUser($req);
        try {
            $data = $this->apiEngine->index(
                $params['appSlug'] ?? '',
                $params['moduleSlug'] ?? '',
                ['page' => $req->query('page', 1), 'per_page' => $req->query('per_page', 25), 'search' => $req->query('search', '')],
                $user['id']
            );
            $this->json($data);
        } catch (\RuntimeException $e) {
            $this->jsonError($e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    public function show(Request $req, array $params): void
    {
        $user = $this->resolveApiUser($req);
        try {
            $data = $this->apiEngine->show($params['appSlug'] ?? '', $params['moduleSlug'] ?? '', (int)($params['id'] ?? 0), $user['id']);
            $status = $data['status'] ?? 200;
            unset($data['status']);
            $this->json($data, $status);
        } catch (\RuntimeException $e) {
            $this->jsonError($e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    public function store(Request $req, array $params): void
    {
        $user = $this->resolveApiUser($req);
        try {
            $payload = $req->json();
            $data    = $this->apiEngine->store($params['appSlug'] ?? '', $params['moduleSlug'] ?? '', $payload, $user['id']);
            $status  = $data['status'] ?? 200;
            unset($data['status']);
            $this->json($data, $status);
        } catch (\RuntimeException $e) {
            $this->jsonError($e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    public function update(Request $req, array $params): void
    {
        $user = $this->resolveApiUser($req);
        try {
            $payload = $req->json();
            $data    = $this->apiEngine->update($params['appSlug'] ?? '', $params['moduleSlug'] ?? '', (int)($params['id'] ?? 0), $payload, $user['id']);
            $this->json($data);
        } catch (\RuntimeException $e) {
            $this->jsonError($e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    public function destroy(Request $req, array $params): void
    {
        $user = $this->resolveApiUser($req);
        try {
            $data = $this->apiEngine->destroy($params['appSlug'] ?? '', $params['moduleSlug'] ?? '', (int)($params['id'] ?? 0), $user['id']);
            $this->json($data);
        } catch (\RuntimeException $e) {
            $this->jsonError($e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    private function resolveApiUser(Request $req): array
    {
        $token = $req->bearerToken();
        if (!$token) { $this->jsonError('Unauthorized — Bearer token required.', 401); }
        $user = $this->apiEngine->resolveUser($token);
        if (!$user) { $this->jsonError('Invalid or expired token.', 401); }
        return $user;
    }
}
