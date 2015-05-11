<?php
namespace Rephlect\Routes;

use Doctrine\Common\Annotations\Reader;
use Rephlect\Annotations\Route as RouteAnnotation;

/**
 * Class Loader
 * @package Rephlect\Routes
 */
class Loader extends \ReflectionClass
{
    /**
     * Constructor.
     *
     * @param string $class A fully qualified class name, per the PSR-4 autoloading standard.
     * @throws \InvalidArgumentException When the parameter is not a string, the class does not exist, or the class is
     * abstract.
     */
    public function __construct($class)
    {
        if (!is_string($class)) {
            throw new \InvalidArgumentException('The parameter must be a string.');
        }

        // provides a better exception than the parent class, which throws a \ReflectionException for the same error
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        parent::__construct($class);

        if ($this->isAbstract()) {
            throw new \InvalidArgumentException(
                sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class)
            );
        }
    }

    /**
     * Returns a collection of {@link Route} objects mapped to the methods in class via the @Route annotation.
     *
     * @param Reader $reader
     * @return Collection
     */
    public function load(Reader $reader)
    {
        $routes = new Collection();

        // the @Route annotations from the class definition, which can serve as defaults for method definitions
        $default = $reader->getClassAnnotation($this, 'Rephlect\Annotations\Route');

        foreach ($this->getMethods() as $method) {
            $handler = $this->getRouteHandler($method);

            foreach ($this->getMethodRouteAnnotations($reader, $method) as $annotation) {
                $routes->add($this->buildRoutes($default, $annotation, $handler));
            }
        }

        return $routes;
    }

    /**
     * Returns only @Route annotations for a method. All other annotations are ignored.
     *
     * @param Reader $reader
     * @param \ReflectionMethod $method
     * @return array
     */
    protected function getMethodRouteAnnotations(Reader $reader, \ReflectionMethod $method)
    {
        $annotations = $reader->getMethodAnnotations($method);
        return array_filter($annotations, function($annotation) {
            return ($annotation instanceof RouteAnnotation);
        });
    }

    /**
     * Returns the method in its callable form.
     *
     * @param \ReflectionMethod $method
     * @return callable
     */
    protected function getRouteHandler(\ReflectionMethod $method)
    {
        // the callback should be called on an instance of the class because static methods suck
        $className = $method->class;
        $obj = new $className();
        return array($obj, $method->name);
    }

    /**
     * Returns an array of Route objects: One for each HTTP verb found in the annotation.
     *
     * First, the annotation is merged into the defaults to produce a comprehensive annotation built from a combination
     * of the class annotation and method annotation. Afterward, the routes are constructed.
     *
     * @param RouteAnnotation $default
     * @param RouteAnnotation $annotation
     * @param callable $handler
     * @return array
     */
    protected function buildRoutes(RouteAnnotation $default, RouteAnnotation $annotation, callable $handler)
    {
        $routes = array();
        $merged = $default->merge($annotation);

        foreach ($merged->verb as $verb) {
            $routes[] = $this->buildRoute($verb, $merged, $handler);
        }

        return $routes;
    }

    /**
     * Returns a single route, which is the verb, path, and handler wired up.
     *
     * @param string $verb
     * @param RouteAnnotation $annotation
     * @param callable $handler
     * @return Route
     */
    protected function buildRoute($verb, RouteAnnotation $annotation, callable $handler)
    {
        // wire up the route and its handler
        $route = new Route($handler, $annotation->path);
        $route->verb = $verb;
        $route->conditions = $annotation->conditions;
        return $route;
    }
}
