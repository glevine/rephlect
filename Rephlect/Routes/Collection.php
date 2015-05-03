<?php
namespace Rephlect\Routes;

class Collection implements \IteratorAggregate, \Countable
{
    protected $routes = array();

    public function getIterator()
    {
        return new \ArrayIterator($this->routes);
    }

    public function count()
    {
        return count($this->routes);
    }

    public function add(Route $route)
    {
        unset($this->routes[$route->path]);
        $this->routes[$route->path] = $route;
    }
}
