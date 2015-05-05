<?php
namespace Rephlect;

use Slim\Middleware;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Rephlect\Routes\Loader as RoutesLoader;

/**
 * Class Rephlect
 * @package Rephlect
 */
class Rephlect extends Middleware
{
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
        $this->app->hook('slim.before.router', array($this, 'mapRoutes'));
        $this->next->call();
    }

    /**
     * Maps the routes from the resources defined during instantiation.
     */
    public function mapRoutes()
    {
        array_walk($this->resources, array($this, 'mapRoute'));
    }

    /**
     * Maps the routes from a single resource.
     *
     * Uses the {@link SimpleAnnotationReader} to parse notations out of the resource's class. Builds the routes and
     * then attaches them to the application.
     *
     * @param string $resource The resource's fully qualified class name, per the PSR-4 autoloading standard.
     */
    public function mapRoute($resource)
    {
        AnnotationRegistry::registerLoader('class_exists');
        $reader = new SimpleAnnotationReader();
        $reader->addNamespace('Rephlect\Annotations');

        $loader = new RoutesLoader($reader);
        $routes = $loader->load($resource);

        foreach ($routes as $route) {
            $route->app = $this->app;
            $this->app->{$route->verb}($route->path, array($route, 'handle'))->conditions($route->conditions);
        }
    }
}
