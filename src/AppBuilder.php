<?php

namespace Core;

class AppBuilder
{
    protected $basePath;
    protected $routingConfig = [];
    protected $middlewareCallback;
    protected $exceptionsCallback;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    public function withRouting($web = null, $commands = null, $health = null)
    {
        $this->routingConfig = [
            'web' => $web,
            'commands' => $commands,
            'health' => $health,
        ];
        return $this;
    }

    public function withMiddleware(callable $callback)
    {
        $this->middlewareCallback = $callback;
        return $this;
    }

    public function withExceptions(callable $callback)
    {
        $this->exceptionsCallback = $callback;
        return $this;
    }

    public function create()
    {
        // 1. App Oluştur
        $app = new App($this->basePath);

        // 2. Rotaları Yükle
        if (!empty($this->routingConfig['web'])) {
            $webRoutes = $this->routingConfig['web'];
            $app->router->registerRoutes($webRoutes);
        }
        
        // Health Check Route (/up)
        if (!empty($this->routingConfig['health'])) {
            $app->router->get($this->routingConfig['health'], function() {
                 header('Content-Type: application/json');
                 echo json_encode(['status' => 'up']);
                 exit;
            });
        }

        // 3. Middleware Yapılandırması (Simüle)
        if ($this->middlewareCallback) {
            $middleware = new Middleware();
            call_user_func($this->middlewareCallback, $middleware);
            // $app->setMiddleware($middleware->getStack()); // İleride eklenebilir
        }

        // 4. Exception Yapılandırması (Simüle)
        if ($this->exceptionsCallback) {
            $exceptions = new Exceptions();
            call_user_func($this->exceptionsCallback, $exceptions);
        }

        return $app;
    }
}
