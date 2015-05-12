<?php
namespace Tests\Rephlect;

use Tests\Rephlect\Helpers\Routes as RoutesHelper;

/**
 * @coversDefaultClass Rephlect\Rephlect
 */
class RephlectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for mapping routes via specific HTTP verbs.
     *
     * @return array
     */
    public function mapRouteProvider()
    {
        return array(
            array('get'),
            array('post'),
            array('put'),
            array('patch'),
            array('delete'),
            array('options'),
            array('any'),
        );
    }

    /**
     * Confirms that the 'slim.before.router' hook is created and that it calls
     * {@link Rephlect\Rephlect::mapResources()}.
     *
     * @covers ::call
     */
    public function testCall()
    {
        $rephlect = $this->getMockBuilder('Rephlect\Rephlect')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $app = $this->getMockBuilder('Slim\Slim')->setMethods(array('hook'))->getMock();
        $app->expects($this->once())
            ->method('hook')
            ->with($this->equalTo('slim.before.router'), $this->equalTo(array($rephlect, 'mapResources')));

        $next = $this->getMockBuilder('Slim\Middleware')->setMethods(array('call'))->getMock();
        $next->expects($this->once())->method('call');

        $rephlect->app = $app;
        $rephlect->next = $next;
        $rephlect->call();
    }

    /**
     * Confirms that {@link Rephlect\Rephlect::mapResource()} is called once for each resource.
     *
     * @covers ::__construct
     * @covers ::mapResources
     */
    public function testMapResources()
    {
        $resources = array('Foo', 'Bar');
        $rephlect = $this->getMockBuilder('Rephlect\Rephlect')
            ->setConstructorArgs(array($resources))
            ->setMethods(array('mapResource'))
            ->getMock();
        $rephlect->expects($this->exactly(2))->method('mapResource');
        $rephlect->mapResources();
    }

    /**
     * Confirms that {@link Rephlect\Rephlect::mapRoute()} is called once for each route whose annotation was parsed
     * from the resource's class.
     *
     * @covers ::mapResource
     * @covers Rephlect\Routes\Route
     * @covers Rephlect\Routes\Collection
     */
    public function testMapResource()
    {
        $helper = new RoutesHelper();

        $routes = $this->getMockBuilder('Rephlect\Routes\Collection')
            ->setMethods(null)
            ->getMock();
        $routes->add(array(
            $helper->getMockRoute($this, '/posts', 'get'),
            $helper->getMockRoute($this, '/posts', 'post'),
            $helper->getMockRoute($this, '/posts/:id', 'get'),
            $helper->getMockRoute($this, '/posts/:id', 'put'),
            $helper->getMockRoute($this, '/posts/:id', 'patch'),
            $helper->getMockRoute($this, '/posts/:id', 'delete'),
        ));

        $rephlect = $this->getMockBuilder('Rephlect\Rephlect')
            ->disableOriginalConstructor()
            ->setMethods(array('getRoutes', 'mapRoute'))
            ->getMock();
        $rephlect->expects($this->any())->method('getRoutes')->willReturn($routes);
        $rephlect->expects($this->exactly(6))->method('mapRoute');
        $rephlect->mapResource('Tests\Rephlect\Helpers\Resource');
    }

    /**
     * Confirms that the proper Slim application convenience method is called to map a route over a standard HTTP verb.
     *
     * @covers ::mapRoute
     * @covers Rephlect\Routes\Route
     * @dataProvider mapRouteProvider
     * @param $verb
     */
    public function testMapRoute($verb)
    {
        $helper = new RoutesHelper();
        $route = $helper->getMockRoute($this, '/posts', $verb);

        $slimRoute = $this->getMockBuilder('Slim\Route')->setMethods(array('via', 'conditions'))->getMock();
        $slimRoute->expects($this->never())->method('via');
        $slimRoute->expects($this->once())->method('conditions');

        $app = $this->getMockBuilder('Slim\Slim')->setMethods(array($verb))->getMock();
        $app->expects($this->once())
            ->method($verb)
            ->with($this->equalTo($route->path), $this->isType('callable'))
            ->willReturn($slimRoute);

        $rephlect = $this->getMockBuilder('Rephlect\Rephlect')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $rephlect->app = $app;
        $rephlect->mapRoute($route);
    }

    /**
     * Confirms that {@link Slim\Slim::map()} and {@link Slim\Route::via()} are called to map a route over a custom HTTP
     * verb. This is the inconvenient route for mapping routes.
     *
     * @covers ::mapRoute
     * @covers Rephlect\Routes\Route
     */
    public function testMapRouteWithCustomVerb()
    {
        $verb = 'custom';
        $helper = new RoutesHelper();
        $route = $helper->getMockRoute($this, '/posts', $verb);

        $slimRoute = $this->getMockBuilder('Slim\Route')->setMethods(array('via', 'conditions'))->getMock();
        $slimRoute->expects($this->once())->method('via');
        $slimRoute->expects($this->once())->method('conditions');

        $app = $this->getMockBuilder('Slim\Slim')->setMethods(array('map'))->getMock();
        $app->expects($this->once())
            ->method('map')
            ->with($this->equalTo($route->path), $this->isType('callable'))
            ->willReturn($slimRoute);

        $rephlect = $this->getMockBuilder('Rephlect\Rephlect')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $rephlect->app = $app;
        $rephlect->mapRoute($route);
    }

    /**
     * Confirms that {@link Rephlect\Rephlect::getRoutes()} loads the routes for a resource and returns them in a
     * collection.
     *
     * @group functional
     * @covers ::call
     *      (It is unclear to me why this coverage annotation is necessary.)
     * @covers ::mapResource
     * @covers ::getRoutes
     * @covers Rephlect\Annotations\Route
     * @covers Rephlect\Annotations\AbstractAnnotation
     * @covers Rephlect\Routes\Loader
     * @covers Rephlect\Routes\Collection
     * @covers Rephlect\Routes\Route
     */
    public function testGetRoutes()
    {
        $rephlect = $this->getMockBuilder('Rephlect\Rephlect')
            ->disableOriginalConstructor()
            ->setMethods(array('mapRoute'))
            ->getMock();
        $rephlect->expects($this->exactly(5))->method('mapRoute');
        $rephlect->mapResource('Tests\Rephlect\Helpers\Resource');
    }
}
