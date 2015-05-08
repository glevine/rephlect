<?php
namespace Tests\Rephlect\Routes;

use Rephlect\Routes\Collection;

/**
 * @coversDefaultClass Rephlect\Routes\Collection
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Confirms that one route can be added to the collection and how the reference key is constructed.
     *
     * @covers ::add
     * @covers Rephlect\Routes\Route
     */
    public function testAddOne()
    {
        $routes = new Collection();
        $route = $this->buildRoute('/posts', 'get');
        $failed = $routes->add($route);

        $collection = new \ReflectionClass($routes);
        $property = $collection->getProperty('routes');
        $property->setAccessible(true);
        $value = $property->getValue($routes);
        $this->assertCount(1, $value);
        $this->assertSame($route, $value['get:/posts']);
        $this->assertCount(0, $failed);
    }

    /**
     * Confirms that multiple routes can be added to the collection at once.
     *
     * @covers ::add
     * @covers Rephlect\Routes\Route
     */
    public function testAddMultiple()
    {
        $routes = new Collection();
        $route1 = $this->buildRoute('/posts', 'get');
        $route2 = $this->buildRoute('/posts', 'post');
        $route3 = $this->buildRoute('/posts/:id', 'get');
        $failed = $routes->add(array($route1, $route2, $route3));

        $collection = new \ReflectionClass($routes);
        $property = $collection->getProperty('routes');
        $property->setAccessible(true);
        $value = $property->getValue($routes);
        $this->assertCount(3, $value);
        $this->assertSame($route1, $value['get:/posts']);
        $this->assertSame($route2, $value['post:/posts']);
        $this->assertSame($route3, $value['get:/posts/:id']);
        $this->assertCount(0, $failed);
    }

    /**
     * Confirms that a new route replaces the old route if their keys collide.
     *
     * @covers ::add
     * @covers Rephlect\Routes\Route
     */
    public function testAddKeyCollision()
    {
        $routes = new Collection();

        $route1 = $this->buildRoute('/posts', 'get');
        $route1->handler = function() {};
        $routes->add($route1);

        $route2 = $this->buildRoute('/posts', 'get');
        $route2->handler = function() {};
        $routes->add($route2);

        $collection = new \ReflectionClass($routes);
        $property = $collection->getProperty('routes');
        $property->setAccessible(true);
        $value = $property->getValue($routes);
        $this->assertCount(1, $value);
        $this->assertSame($route2->handler, $value['get:/posts']->handler);
    }

    /**
     * Confirms that any routes that could not be added are returned.
     *
     * @covers ::add
     * @covers Rephlect\Routes\Route
     */
    public function testAddInvalidParameter()
    {
        $routes = new Collection();
        $failed = $routes->add('foo');

        $collection = new \ReflectionClass($routes);
        $property = $collection->getProperty('routes');
        $property->setAccessible(true);
        $value = $property->getValue($routes);
        $this->assertCount(0, $value);
        $this->assertCount(1, $failed);
    }

    /**
     * Confirms that the collection is countable.
     *
     * @covers ::add
     * @covers ::count
     * @covers Rephlect\Routes\Route
     */
    public function testCount()
    {
        $routes = new Collection();
        $routes->add($this->buildRoute('/posts', 'get'));
        $routes->add($this->buildRoute('/posts', 'post'));
        $routes->add($this->buildRoute('/posts/:id', 'get'));
        $routes->add($this->buildRoute('/posts/:id', 'put'));
        $routes->add($this->buildRoute('/posts/:id', 'patch'));
        $routes->add($this->buildRoute('/posts/:id', 'delete'));
        $this->assertCount(6, $routes);
    }

    /**
     * Confirms that the collection is iterable.
     *
     * @covers ::add
     * @covers ::getIterator
     * @covers Rephlect\Routes\Route
     */
    public function testIterator()
    {
        $routes = array(
            $this->buildRoute('/posts', 'get'),
            $this->buildRoute('/posts', 'post'),
            $this->buildRoute('/posts/:id', 'get'),
            $this->buildRoute('/posts/:id', 'put'),
            $this->buildRoute('/posts/:id', 'patch'),
            $this->buildRoute('/posts/:id', 'delete'),
        );

        $collection = new Collection();
        $collection->add($routes[0]);
        $collection->add($routes[1]);
        $collection->add($routes[2]);
        $collection->add($routes[3]);
        $collection->add($routes[4]);
        $collection->add($routes[5]);

        $i = 0;

        foreach ($collection as $route) {
            $this->assertSame($routes[$i++], $route);
        }
    }

    /**
     * Returns a mock route that does not call the original constructor.
     *
     * @param string $path
     * @param string $verb
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function buildRoute($path, $verb)
    {
        $route = $this->getMockBuilder('Rephlect\Routes\Route')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $route->path = $path;
        $route->verb = $verb;
        return $route;
    }
}
