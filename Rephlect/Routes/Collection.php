<?php
namespace Rephlect\Routes;

class Collection
{
    protected $routes = array();

    public function add(Route $route)
    {
        unset($this->routes[$route->path]);
        $this->routes[$route->path] = $route;
    }
}
