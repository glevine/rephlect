<?php
namespace Rephlect;

use Slim\Middleware;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Rephlect\Routes\Loader as RoutesLoader;

class Rephlect extends Middleware
{
    protected $resources = array();

    public function __construct(array $resources)
    {
        $this->resources = $resources;
    }

    public function call()
    {
        $this->app->hook('slim.before.router', array($this, 'mapRoutes'));
        $this->next->call();
    }

    public function mapRoutes()
    {
        array_walk($this->resources, array($this, 'mapRoute'));
    }

    public function mapRoute($resource)
    {
        AnnotationRegistry::registerAutoloadNamespace('Rephlect\Annotations');
        $reader = new SimpleAnnotationReader();
        $reader->addNamespace('Rephlect\Annotations');

        $loader = new RoutesLoader($reader);
        $routes = $loader->load($resource);

        foreach ($routes as $route) {
            $route->app = $this->app;
            $this->app->{$route->verb}($route->path, array($route, 'handle'));
        }
    }
}
