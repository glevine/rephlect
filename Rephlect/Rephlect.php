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
     * Uses the {@link SimpleAnnotationReader} to parse notations out of the resource's class. Builds the routes and
     * then attaches them to the application.
     *
     * @param string $resource The resource's fully qualified class name, per the PSR-4 autoloading standard.
     */
    public function mapResource($resource)
    {
        AnnotationRegistry::registerLoader('class_exists');
        $reader = new SimpleAnnotationReader();
        $reader->addNamespace('Rephlect\Annotations');

        $loader = new RoutesLoader($reader);
        $routes = $loader->load($resource);

        /**
         * The callable passed to iterator_apply must return true in order to continue applying the function. Since
         * neither Rephlect::mapResources() nor Rephlect::mapResource() return anything, it would be awkward if
         * Rephlect::mapRoute() had to. Wrap the call to Rephlect::mapRoute() so that Rephlect::mapRoute() can be a void
         * function, which keeps the API consistent in the event that Rephlect::mapRoute() is used outside this class.
         */
        $self = $this;
        iterator_apply($routes, function(Route $route) use ($self) {
            $self->mapRoute($route);
            return true;
        });
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
}
