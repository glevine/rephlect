<?php
namespace Rephlect\Routes;

/**
 * Class Collection
 * @package Rephlect\Routes
 */
class Collection implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     * Array containing all {@link Route} objects in the collection.
     */
    protected $routes = array();

    /**
     * Enables iterating over the routes in the collection.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->routes);
    }

    /**
     * Returns the number of routes in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->routes);
    }

    /**
     * Adds a new route to the collection with a unique key.
     *
     * Replaces a route in the case of a key collision. The key is computed with the route's verb and path separated by
     * a colon (e.g., "put:/Foo/:id").
     *
     * @param Route $route
     */
    public function add(Route $route)
    {
        $key = "{$route->verb}:{$route->path}";
        unset($this->routes[$key]);
        $this->routes[$key] = $route;
    }
}
