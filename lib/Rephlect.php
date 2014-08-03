<?php
namespace Rephlect;

use \Slim\Slim;

class Rephlect
{
    private $app;

    public function __construct(Slim $app)
    {
        $this->app = $app;
    }

    public function addResource($name)
    {
        $class = new \ReflectionClass($name);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $methodName = $method->getName();
            $this->app->get(
                "/{$name}/{$methodName}",
                function() use ($name, $methodName) {
                    $obj = new $name();
                    echo $obj->$methodName();
                }
            );
        }
    }
}
