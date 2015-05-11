<?php
namespace Tests\Rephlect\Routes;

use Rephlect\Routes\Loader;

/**
 * @coversDefaultClass Rephlect\Routes\Loader
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testLoaderClassIsNotAString()
    {
        $loader = new Loader(10);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testLoaderClassDoesNotExist()
    {
        $loader = new Loader('Foo');
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testLoaderClassIsAbstract()
    {
        $loader = new Loader('Rephlect\Annotations\AbstractAnnotation');
    }

    /**
     * @covers ::__construct
     * @covers ::load
     * @covers ::getMethodRouteAnnotations
     * @covers ::getRouteHandler
     * @covers ::buildRoutes
     * @covers ::buildRoute
     * @covers Rephlect\Annotations\AbstractAnnotation
     * @covers Rephlect\Annotations\Route
     * @covers Rephlect\Routes\Route
     * @covers Rephlect\Routes\Collection
     */
    public function testLoad()
    {
        // the default path is /posts
        $classAnnotation = $this->getMockBuilder('Rephlect\Annotations\Route')
            ->setConstructorArgs(array(
                array('path' => '/posts'),
            ))
            ->setMethods(null)
            ->getMock();
        // the create method is used on POST requests to /posts
        $methodAnnotation1 = $this->getMockBuilder('Rephlect\Annotations\Route')
            ->setConstructorArgs(array(
                array('verb' => 'post'),
            ))
            ->setMethods(null)
            ->getMock();
        // the read method is used on GET requests to /posts/:id
        $methodAnnotation2 = $this->getMockBuilder('Rephlect\Annotations\Route')
            ->setConstructorArgs(array(
                array('path' => '/:id'),
            ))
            ->setMethods(null)
            ->getMock();
        // the update method is used on PUT and PATCH requests to /posts/:id
        $methodAnnotation3 = $this->getMockBuilder('Rephlect\Annotations\Route')
            ->setConstructorArgs(array(
                array('path' => '/:id', 'verb' => array('put', 'patch')),
            ))
            ->setMethods(null)
            ->getMock();

        $reader = $this->getMockBuilder('Doctrine\Common\Annotations\SimpleAnnotationReader')
            ->setMethods(array('getClassAnnotation', 'getMethodAnnotations'))
            ->disableOriginalConstructor()
            ->getMock();
        $reader->expects($this->once())->method('getClassAnnotation')->willReturn($classAnnotation);
        $reader->expects($this->exactly(3))
            ->method('getMethodAnnotations')
            ->willReturnOnConsecutiveCalls(
                array($methodAnnotation1),
                array($methodAnnotation2),
                array($methodAnnotation3)
            );

        $method1 = $this->getMockBuilder('ReflectionMethod')
            ->setConstructorArgs(array('Tests\Rephlect\Helpers\Resource', 'create'))
            ->getMock();
        $method2 = $this->getMockBuilder('ReflectionMethod')
            ->setConstructorArgs(array('Tests\Rephlect\Helpers\Resource', 'read'))
            ->getMock();
        $method3 = $this->getMockBuilder('ReflectionMethod')
            ->setConstructorArgs(array('Tests\Rephlect\Helpers\Resource', 'update'))
            ->getMock();

        $loader = $this->getMockBuilder('Rephlect\Routes\Loader')
            ->setConstructorArgs(array('Tests\Rephlect\Helpers\Resource'))
            ->setMethods(array('getMethods'))
            ->getMock();
        $loader->expects($this->once())->method('getMethods')->willReturn(array($method1, $method2, $method3));

        $routes = $loader->load($reader);
        $this->assertCount(4, $routes);

        // access the routes like a normal array
        $routes = $routes->getIterator();
        // post
        $this->assertSame('/posts', $routes['post:/posts']->path);
        $this->assertSame('post', $routes['post:/posts']->verb);
        // get
        $this->assertSame('/posts/:id', $routes['get:/posts/:id']->path);
        $this->assertSame('get', $routes['get:/posts/:id']->verb);
        // put
        $this->assertSame('/posts/:id', $routes['put:/posts/:id']->path);
        $this->assertSame('put', $routes['put:/posts/:id']->verb);
        // patch
        $this->assertSame('/posts/:id', $routes['patch:/posts/:id']->path);
        $this->assertSame('patch', $routes['patch:/posts/:id']->verb);
    }

    /**
     * @covers ::__construct
     * @covers ::load
     * @covers ::getMethodRouteAnnotations
     * @covers Rephlect\Annotations\AbstractAnnotation
     * @covers Rephlect\Annotations\Route
     * @covers Rephlect\Routes\Route
     * @covers Rephlect\Routes\Collection
     */
    public function testLoadNonRouteAnnotationsAreSkipped()
    {
        // the default path is /posts
        $classAnnotation = $this->getMockBuilder('Rephlect\Annotations\Route')
            ->setConstructorArgs(
                array(
                    array('path' => '/posts'),
                )
            )
            ->setMethods(null)
            ->getMock();
        // @ConcreteAnnotation instead of @Route
        $methodAnnotation = $this->getMockBuilder('Tests\Rephlect\Helpers\ConcreteAnnotation')
            ->setConstructorArgs(array(array()))
            ->setMethods(null)
            ->getMock();

        $reader = $this->getMockBuilder('Doctrine\Common\Annotations\SimpleAnnotationReader')
            ->setMethods(array('getClassAnnotation', 'getMethodAnnotations'))
            ->disableOriginalConstructor()
            ->getMock();
        $reader->expects($this->once())->method('getClassAnnotation')->willReturn($classAnnotation);
        $reader->expects($this->once())->method('getMethodAnnotations')->willReturn(array($methodAnnotation));

        $method = $this->getMockBuilder('ReflectionMethod')
            ->setConstructorArgs(array('Tests\Rephlect\Helpers\Resource', 'create'))
            ->getMock();

        $loader = $this->getMockBuilder('Rephlect\Routes\Loader')
            ->setConstructorArgs(array('Tests\Rephlect\Helpers\Resource'))
            ->setMethods(array('getMethods', 'getRouteHandler', 'buildRoutes', 'buildRoute'))
            ->getMock();
        $loader->expects($this->once())->method('getMethods')->willReturn(array($method));
        $loader->expects($this->once())->method('getRouteHandler');
        $loader->expects($this->never())->method('buildRoutes');
        $loader->expects($this->never())->method('buildRoute');

        $routes = $loader->load($reader);
        $this->assertCount(0, $routes);
    }
}
