<?php
namespace Rephlect;

use Slim\Slim;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Rephlect\Routes\Loader as RoutesLoader;

class Rephlect
{
    protected $app;

    public function __construct(Slim $app)
    {
        $this->app = $app;
    }

    public function addResource($name)
    {
        AnnotationRegistry::registerAutoloadNamespace('Rephlect\Annotations');
        $reader = new SimpleAnnotationReader();
        $reader->addNamespace('Rephlect\Annotations');

        $loader = new RoutesLoader($reader);
        $routes = $loader->load($name);

        foreach ($routes as $route) {
            $route->app = $this->app;
            $this->app->{$route->verb}($route->path, array($route, 'handle'));
        }
    }
}
