<?php
namespace App\Core;

use App\Middlewares\Middleware;

class Router
{
    private $routes = [];
    private $middlewares = [];
    private $groupMiddleware = [];
    private $groupPrefix = '';
    
    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    public function put(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    public function delete(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    public function match(array $methods, string $path, $handler, array $middleware = []): void
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $path, $handler, $middleware);
        }
    }
    
    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;
        
        $this->groupPrefix .= $prefix;
        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        
        call_user_func($callback, $this);
        
        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }
    
    private function addRoute(string $method, string $path, $handler, array $middleware = []): void
    {
        $path = $this->groupPrefix . $path;
        $middleware = array_merge($this->groupMiddleware, $middleware);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    public function dispatch(Request $request, Response $response): void
    {
        $path = $request->getPath();
        $method = $request->getMethod();
        
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route, $method, $path, $params)) {
                // Run middlewares
                foreach ($route['middleware'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle($request, $response)) {
                        return;
                    }
                }
                
                // Execute handler
                $this->executeHandler($route['handler'], $request, $response, $params);
                return;
            }
        }
        
        // 404 Not Found
        $response->setStatusCode(404);
        $app = Application::getInstance();
        $view = $app->getService('view');
        echo $view->render('errors/404');
    }
    
    private function matchRoute(array $route, string $method, string $path, &$params): bool
    {
        if ($route['method'] !== $method) {
            return false;
        }
        
        $pattern = $this->convertPathToRegex($route['path']);
        return preg_match($pattern, $path, $params) === 1;
    }
    
    private function convertPathToRegex(string $path): string
    {
        $path = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $path . '$#';
    }
    
    private function executeHandler($handler, Request $request, Response $response, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, [$request, $response, $params]);
        } elseif (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);
            $controller = "App\\Controllers\\{$controller}";
            
            if (class_exists($controller)) {
                $controllerInstance = new $controller();
                if (method_exists($controllerInstance, $method)) {
                    $controllerInstance->$method($request, $response, $params);
                    return;
                }
            }
        }
        
        throw new \Exception("Invalid route handler");
    }
}