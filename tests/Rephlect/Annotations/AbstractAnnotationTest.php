<?php
namespace Tests\Rephlect\Annotations;

use Tests\Rephlect\Helpers\ConcreteAnnotation;

/**
 * @coversDefaultClass Rephlect\Annotations\AbstractAnnotation
 */
class AbstractAnnotationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Confirms that the properties of the annotation class will be set when passed in to the constructor.
     *
     * @covers ::__construct
     * @covers ::__get
     * @covers ::__set
     */
    public function testConstructor()
    {
        $annotation = new ConcreteAnnotation(array('bar' => 'biz'));
        $this->assertSame('biz', $annotation->bar);
    }

    /**
     * Confirms that a property's getter and setter will be called if the annotation class implements them.
     *
     * @covers ::__get
     * @covers ::__set
     * @covers Tests\Rephlect\Helpers\ConcreteAnnotation::getFoo
     * @covers Tests\Rephlect\Helpers\ConcreteAnnotation::setFoo
     */
    public function testGetterAndSetter()
    {
        $annotation = $this->getMockBuilder('Tests\Rephlect\Helpers\ConcreteAnnotation')
            ->setMethods(array('getFoo', 'setFoo'))
            ->disableOriginalConstructor()
            ->getMock();
        $annotation->expects($this->once())->method('getFoo');
        $annotation->expects($this->once())->method('setFoo');

        // should call ConcreteAnnotation::setFoo
        $annotation->foo = 'baz';
        // should call ConcreteAnnotation::getFoo
        $value = $annotation->foo;
    }
}
