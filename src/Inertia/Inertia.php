<?php

namespace Inertia;

class Inertia
{
    /**
     * @param string $component
     * @param array $props
     * @return mixed
     */
    public static function render($component, $props = [])
    {
        return \Core\Inertia::render($component, $props);
    }
}
