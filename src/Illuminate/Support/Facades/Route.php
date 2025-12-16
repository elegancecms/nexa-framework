<?php

namespace Illuminate\Support\Facades;

use Core\Router;

/**
 * @method static \Core\Route get(string $uri, array|string|callable|null $action = null)
 * @method static \Core\Route post(string $uri, array|string|callable|null $action = null)
 * @method static \Core\Route put(string $uri, array|string|callable|null $action = null)
 * @method static \Core\Route patch(string $uri, array|string|callable|null $action = null)
 * @method static \Core\Route delete(string $uri, array|string|callable|null $action = null)
 * @method static \Core\Route redirect(string $uri, string $destination, int $status = 302)
 * @method static void group(array $attributes, \Closure $callback)
 */
class Route
{
    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = Router::getInstance();

        return $instance->$method(...$args);
    }
}
