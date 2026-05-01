<?php

namespace Core;

/**
 * Router — URL dispatcher.
 *
 * Supports GET, POST, PUT, DELETE verbs and {param} placeholders.
 * Method tunneling via _method POST field (for PUT/DELETE in HTML forms).
 */
class Router
{
    /** @var array<string, array{pattern: string, handler: string}[]> */
    private array $routes = [];

    private ?\Plugins\PluginManager $plugins;

    public function __construct(?\Plugins\PluginManager $plugins = null)
    {
        $this->plugins = $plugins;
    }

    // ── Route registration helpers ───────────────────────────

    public function get(string $pattern, string $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, string $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    public function put(string $pattern, string $handler): void
    {
        $this->addRoute('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, string $handler): void
    {
        $this->addRoute('DELETE', $pattern, $handler);
    }

    public function match(array $methods, string $pattern, string $handler): void
    {
        foreach ($methods as $m) {
            $this->addRoute(strtoupper($m), $pattern, $handler);
        }
    }

    private function addRoute(string $method, string $pattern, string $handler): void
    {
        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    // ── Dispatch ─────────────────────────────────────────────

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri    = $request->uri();

        // Allow HTML forms to tunnel PUT/DELETE via hidden _method field
        if ($method === 'POST' && $request->post('_method')) {
            $tunneled = strtoupper($request->post('_method'));
            if (in_array($tunneled, ['PUT', 'DELETE', 'PATCH'], true)) {
                $method = $tunneled;
            }
        }

        $routeList = $this->routes[$method] ?? [];

        foreach ($routeList as $route) {
            $params = $this->matchPattern($route['pattern'], $uri);
            if ($params !== null) {
                // Fire pre-dispatch hook
                if ($this->plugins) {
                    $this->plugins->fire('before_dispatch', ['route' => $route, 'params' => $params]);
                }

                $this->callHandler($route['handler'], $params, $request);
                return;
            }
        }

        // 404 fallback
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }

    /**
     * Match a URI against a route pattern.
     * Returns associative array of captured params, or null on no match.
     *
     * Pattern: /apps/{appId}/modules/{id}
     * URI:     /apps/3/modules/7
     * Returns: ['appId' => '3', 'id' => '7']
     */
    private function matchPattern(string $pattern, string $uri): ?array
    {
        // Convert pattern placeholders to named regex groups
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            // Extract only named captures
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return null;
    }

    /**
     * Instantiate the controller and call the action method.
     *
     * Handler format: 'ControllerName@methodName'
     */
    private function callHandler(string $handler, array $params, Request $request): void
    {
        [$controllerName, $method] = explode('@', $handler);
        $fqcn = "Controllers\\{$controllerName}";

        if (!class_exists($fqcn)) {
            throw new \RuntimeException("Controller [{$fqcn}] not found.");
        }

        $controller = new $fqcn(getDB());

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method [{$method}] not found on [{$fqcn}].");
        }

        // Inject URL params into request for controllers to access
        $request->setRouteParams($params);
        $controller->$method($request, $params);
    }
}
