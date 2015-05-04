<?php
namespace Rephlect\Routes;

use Doctrine\Common\Annotations\Reader;

/**
 * Class Loader
 * @package Rephlect\Routes
 */
class Loader
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var string
     * The class that contains the annotation definition.
     */
    protected $annotationClass = 'Rephlect\\Annotations\\Route';

    /**
     * Constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Returns a collection of {@link Route} objects mapped to the methods in class via the @Route annotation.
     *
     * @param string $class A fully qualified class name, per the PSR-4 autoloading standard.
     * @return Collection
     * @throws \InvalidArgumentException When the class does not exist or when the class is abstract.
     */
    public function load($class)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new \ReflectionClass($class);

        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(
                sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class)
            );
        }

        $globals = $this->getGlobals($class);
        $routes = new Collection();

        foreach ($class->getMethods() as $method) {
            $methodAnnotations = $this->reader->getMethodAnnotations($method);

            foreach ($methodAnnotations as $annotation) {
                if ($annotation instanceof $this->annotationClass) {
                    // append the path from the method annotation to that from the class annotation to get the full path
                    $path = $globals['path'] . $annotation->path;

                    // the callback should be called on an instance of the class because static methods suck
                    $klass = $method->class;
                    $obj = new $klass();
                    $callback = array($obj, $method->name);

                    // wire up the route and its handler and add it the collection of routes
                    $route = new Route($callback, $path);
                    $route->verb = $annotation->verb;
                    $routes->add($route);
                }
            }
        }

        return $routes;
    }

    /**
     * Returns the @Route annotations from the class definition, which can server as defaults for method definitions.
     *
     * @param \ReflectionClass $class
     * @return array
     */
    protected function getGlobals(\ReflectionClass $class)
    {
        $globals = array('path' => '');
        $classAnnotations = $this->reader->getClassAnnotation($class, $this->annotationClass);

        if (!$class) {
            return $globals;
        }

        if (!is_null($classAnnotations->path)) {
            $globals['path'] = $classAnnotations->path;
        }

        return $globals;
    }
}
