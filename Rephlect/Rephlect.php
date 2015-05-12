<?php
namespace Rephlect;

use Slim\Middleware;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Rephlect\Routes\Loader as RoutesLoader;
use Rephlect\Routes\Route;

/**
 * Class Rephlect
 * @package Rephlect
 */
class Rephlect extends Middleware
{
    /**
     * @var array
     * The standard HTTP verbs (methods) for which Slim has convenience methods.
     */
    protected static $verbs = array('get', 'post', 'put', 'patch', 'delete', 'options', 'any');

    /**
     * @var array
     * The list of full-qualified class names for resources whose methods should be mapped to routes when the
     * application runs.
     */
    protected $resources = array();

    /**
     * Constructor.
     *
     * @param array $resources Each element should be the fully qualified class name -- per the PSR-4 autoloading
     * standard -- of a resource.
     */
    public function __construct(array $resources)
    {
        $this->resources = $resources;
    }

    /**
     * {@inheritDoc}
     *
     * Maps the routes from the resources defined during instantiation and calls the next middleware.
     */
    public function call()
    {
        $this->app->hook('slim.before.router', array($this, 'mapResources'));
        $this->next->call();
    }

    /**
     * Maps the routes from the resources defined during instantiation.
     */
    public function mapResources()
    {
        array_walk($this->resources, array($this, 'mapResource'));
    }

    /**
     * Maps the routes from a single resource.
     *
     * @param string $resource The resource's fully qualified class name, per the PSR-4 autoloading standard.
     */
    public function mapResource($resource)
    {
        $self = $this;
        $map = function (\Iterator $it) use ($self) {
            $self->mapRoute($it->current());
            return true;
        };

        $routes = $this->getRoutes($resource);
        /**
         * The callable passed to iterator_apply must return true in order to continue applying the function. Since
         * neither Rephlect::mapResources() nor Rephlect::mapResource() return anything, it would be awkward if
         * Rephlect::mapRoute() had to. Wrapping the call to Rephlect::mapRoute() so that Rephlect::mapRoute() can be a
         * void function, which keeps the API consistent in the event that Rephlect::mapRoute() is used outside this
         * class.
         */
        iterator_apply($routes, $map, array($routes->getIterator()));
    }

    /**
     * Maps a single route to the application.
     *
     * @param Route $route
     */
    public function mapRoute(Route $route)
    {
        $route->app = $this->app;

        // use Slim::map() if it's a custom verb
        $isCustomVerb = !in_array($route->verb, static::$verbs);
        $method = $isCustomVerb ? 'map' : $route->verb;
        $mappedRoute = $this->app->{$method}($route->path, array($route, 'handle'));

        // need to specify the verb on the route when it's custom
        if ($isCustomVerb) {
            $mappedRoute->via(strtoupper($route->verb));
        }

        $mappedRoute->conditions($route->conditions);
    }

    /**
     * Uses the {@link SimpleAnnotationReader} to parse notations out of the resource's class. Builds the routes and
     * returns the collection.
     *
     * @param string $resource The resource's fully qualified class name, per the PSR-4 autoloading standard.
     * @return Routes\Collection
     */
    protected function getRoutes($resource)
    {
        AnnotationRegistry::registerLoader('class_exists');
        $reader = new SimpleAnnotationReader();
        $reader->addNamespace('Rephlect\Annotations');

        $loader = new RoutesLoader($resource);
        return $loader->load($reader);
    }
}
