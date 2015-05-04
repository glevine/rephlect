<?php
namespace Rephlect\Routes;

use Doctrine\Common\Annotations\Reader;

class Loader
{
    protected $reader;
    protected $annotationClass = 'Rephlect\\Annotations\\Route';

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

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
                    $path = $globals['path'] . $annotation->path;

                    $klass = $method->class;
                    $obj = new $klass();
                    $callback = array($obj, $method->name);

                    $route = new Route($path, $callback);
                    $route->verb = $annotation->verb;
                    $routes->add($route);
                }
            }
        }

        return $routes;
    }

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
