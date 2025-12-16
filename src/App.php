<?php

namespace Core;

class App
{
    public Router $router;
    protected $basePath;

    public function __construct($basePath = null)
    {
        $this->basePath = $basePath;
        $this->router = Router::getInstance();
    }

    public function registerRoutes($path)
    {
        $router = $this->router;
        if (file_exists($path)) {
            require $path;
        }
    }

    public function run()
    {
        echo $this->router->dispatch();
    }

    public static function configure($basePath)
    {
        return new AppBuilder($basePath);
    }
}
