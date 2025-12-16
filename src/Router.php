<?php

namespace Core;

class Router
{
    protected static $instance;
    protected $routes = [];
    protected $groupStack = [];

    public function __construct()
    {
        self::$instance = $this;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    // Facade-like static access (Route::get(...) -> $instance->get(...))
    public static function __callStatic($method, $args)
    {
        return self::getInstance()->$method(...$args);
    }

    public function registerRoutes($path)
    {
        if (file_exists($path)) {
            require $path;
        }
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function group($attributes, $callback)
    {
        $this->groupStack[] = $attributes;
        call_user_func($callback);
        array_pop($this->groupStack);
    }

    protected function addRoute($method, $uri, $action)
    {
        // 1. Grup özelliklerini (prefix, middleware) al
        $attributes = $this->mergeGroupAttributes();

        // 2. URI Prefix
        if (isset($attributes['prefix'])) {
            $uri = rtrim($attributes['prefix'], '/') . '/' . ltrim($uri, '/');
        }

        // 3. Routu oluştur
        $route = new Route($method, $uri, $action);

        // 4. Grup Middleware'lerini ekle
        if (isset($attributes['middleware'])) {
            $route->middleware($attributes['middleware']);
        }

        $this->routes[] = $route;

        return $route;
    }

    protected function mergeGroupAttributes()
    {
        $merged = [];
        foreach ($this->groupStack as $group) {
            foreach ($group as $key => $value) {
                if ($key === 'middleware') {
                    $merged['middleware'] = array_merge($merged['middleware'] ?? [], (array)$value);
                } elseif ($key === 'prefix') {
                    $merged['prefix'] = ($merged['prefix'] ?? '') . '/' . trim($value, '/');
                } else {
                    $merged[$key] = $value;
                }
            }
        }
        return $merged;
    }

    // --- HTTP Verbs ---

    public function get($uri, $action)
    {
        return $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function put($uri, $action)
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function patch($uri, $action)
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    public function delete($uri, $action)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    public function redirect($uri, $destination, $status = 302)
    {
        return $this->get($uri, function() use ($destination, $status) {
            header("Location: $destination", true, $status);
            exit;
        });
    }

    // --- Dispatcher ---

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route->method === $method && $this->match($route->uri, $uri, $params)) {
                
                // Middleware Check (Basit simülasyon)
                if (!empty($route->middlewares)) {
                    // Auth kontrolü vb. burada yapılabilir.
                    // Şimdilik sadece logluyoruz.
                    // error_log('Middleware check: ' . implode(', ', $route->middlewares));
                }

                return $this->handleAction($route->action, $params);
            }
        }

        // 404
        http_response_code(404);
        echo "404 Not Found";
    }

    protected function match($routeUri, $requestUri, &$params)
    {
        // Basit regex eşleştirme ({id} gibi parametreleri yakalamak için)
        $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routeUri) . "$@D";
        
        if (preg_match($pattern, $requestUri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }
        return false;
    }

    protected function handleAction($action, $params)
    {
        if (is_callable($action)) {
            return call_user_func_array($action, $params);
        }

        if (is_array($action)) {
            [$controller, $method] = $action;
            $instance = new $controller();
            return call_user_func_array([$instance, $method], $params);
        }
    }
}
