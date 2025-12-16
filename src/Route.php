<?php

namespace Core;

class Route
{
    public $method;
    public $uri;
    public $action;
    public $name;
    public $middlewares = [];

    public function __construct($method, $uri, $action)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->action = $action;
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function middleware($middleware)
    {
        if (is_array($middleware)) {
            $this->middlewares = array_merge($this->middlewares, $middleware);
        } else {
            $this->middlewares[] = $middleware;
        }
        return $this;
    }
}
