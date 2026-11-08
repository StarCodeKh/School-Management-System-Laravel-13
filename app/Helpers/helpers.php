<?php

if (! function_exists('set_active')) {
    /**
     * Return "active" when the current route name matches one of the given
     * route names (the sidebar menu stores route names, not URL paths).
     */
    function set_active(string|array|null $routes): string
    {
        $routes = array_filter((array) $routes);

        return $routes && request()->routeIs(...$routes) ? 'active' : '';
    }
}
