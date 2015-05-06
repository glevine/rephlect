<?php
namespace Tests\Rephlect\Helpers;

use Rephlect\Annotations\AbstractAnnotation;

class ConcreteAnnotation extends AbstractAnnotation
{
    protected $foo;
    protected $bar;

    protected function getFoo()
    {
        return $this->foo;
    }

    protected function setFoo($foo)
    {
        $this->foo = $foo;
    }
}
