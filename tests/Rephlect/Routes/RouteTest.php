<?php
namespace Tests\Rephlect\Routes;

use Rephlect\Routes\Route;

/**
 * @coversDefaultClass Rephlect\Routes\Route
 */
class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for the Route constructor.
     *
     * @return array
     */
    public function constructorProvider()
    {
        /**
         * A bare-bones callable that every test case can use.
         */
        $handler = function () {};

        return array(
            // path is specified
            array($handler, '/posts/:id', '/posts/:id'),
            // path is not specified
            array($handler, null, '/'),
        );
    }

    /**
     * Data provider for setting the path.
     *
     * @return array
     */
    public function setPathProvider()
    {
        return array(
            // path is not specified
            array(null, '/'),
            // path is specified
            array('posts', '/posts'),
            // path starts with a /
            array('/posts', '/posts'),
            // path starts with //
            array('//posts', '/posts'),
            // path starts with multiple /
            array('/////posts', '/posts'),
            // path is surrounded by whitespace
            array('  /posts  ', '/posts'),
            // path ends with a /
            array('/posts/', '/posts/'),
        );
    }

    /**
     * Data provider for setting the verb.
     *
     * @return array
     */
    public function setVerbProvider()
    {
        return array(
            // verb is not specified
            array(null, 'get'),
            array('', 'get'),
            array('    ', 'get'),
            // verb is specified
            array('post', 'post'),
            array('put', 'put'),
            array('patch', 'patch'),
            array('delete', 'delete'),
            // verb is surrounded by whitespace
            array('  get ', 'get'),
            array('     post    ', 'post'),
        );
    }

    /**
     * Data provider for setting the verb with an invalid parameter.
     *
     * @return array
     */
    public function setVerbThrowsExceptionProvider()
    {
        return array(
            array(10),
            array(array('put', 'patch')),
            array(new \stdClass()),
        );
    }

    /**
     * Data provider for invoking the handler without any request parameters.
     *
     * @return array
     */
    public function handleWithoutArgumentsProvider()
    {
        return array(
            // empty request body; like a GET (all)
            array(null),
            array(''),
            array(array()),
            // non-empty request body; like a POST
            array(
                'subject' => 'Foo Bar',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis posuere.',
            ),
        );
    }

    /**
     * Data provider for invoking the handler with a request parameter.
     *
     * @return array
     */
    public function handleWithArgumentsProvider()
    {
        return array(
            // empty request body; like a GET (one)
            array(12, null),
            array(4, ''),
            array('abc', array()),
            // non-empty request body; like a PUT
            array(
                'xyz',
                array(
                    'subject' => 'Foo Bar',
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis posuere.',
                ),
            ),
        );
    }

    /**
     * Confirms that the path will either be the default or a specified value.
     *
     * @covers ::__construct
     * @covers ::__get
     * @covers ::__set
     * @covers ::setHandler
     * @covers ::setPath
     * @dataProvider constructorProvider
     * @param callable $handler
     * @param string $path
     * @param string $expected
     */
    public function testRoute($handler, $path, $expected)
    {
        $route = new Route($handler, $path);
        $this->assertSame($expected, $route->path);
    }

    /**
     * Confirms that the magic {@link Route::__get()} and {@link Route::__set()} methods return and set a property,
     * respectively.
     *
     * @covers ::__get
     * @covers ::__set
     */
    public function testGetterAndSetter()
    {
        $conditions = array('id' => '\d+');
        $route = $this->getMockBuilder('Rephlect\Routes\Route')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $route->conditions = $conditions;
        $this->assertSame($conditions, $route->conditions);
    }

    /**
     * Confirms that {@link Route::handler} is set using {@link Route::setHandler()}.
     *
     * @covers ::__set
     * @covers ::setHandler
     */
    public function testSetHandler()
    {
        $handler = function() {};
        $route = $this->getMockBuilder('Rephlect\Routes\Route')
            ->setMethods(array('setHandler'))
            ->disableOriginalConstructor()
            ->getMock();
        $route->expects($this->once())->method('setHandler')->with($handler);
        $route->handler = $handler;
    }

    /**
     * Confirms that {@link Route::path} is set using {@link Route::setPath()} and tests its implementation.
     *
     * @covers ::__get
     * @covers ::__set
     * @covers ::setPath
     * @dataProvider setPathProvider
     * @param string $path
     * @param string $expected
     */
    public function testSetPath($path, $expected)
    {
        $route = $this->getMockBuilder('Rephlect\Routes\Route')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $route->path = $path;
        $this->assertSame($expected, $route->path);
    }

    /**
     * Confirms that {@link Route::verb} is set using {@link Route::setVerb()} and tests its implementation.
     *
     * @covers ::__get
     * @covers ::__set
     * @covers ::setVerb
     * @dataProvider setVerbProvider
     * @param string|array $verb
     * @param array $expected
     */
    public function testSetVerb($verb, $expected)
    {
        $route = $this->getMockBuilder('Rephlect\Routes\Route')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $route->verb = $verb;
        $this->assertSame($expected, $route->verb);
    }

    /**
     * Confirms that an exception will be thrown when trying to set {@see Route::verb} to a value that is not a string.
     *
     * @covers ::__set
     * @covers ::setVerb
     * @dataProvider setVerbThrowsExceptionProvider
     * @param mixed $verb
     * @expectedException \InvalidArgumentException
     */
    public function testSetVerbThrowsException($verb)
    {
        $route = $this->getMockBuilder('Rephlect\Routes\Route')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $route->verb = $verb;
    }

    /**
     * Confirms that invoking the handler without any request parameters will result in calling the route handler
     * appropriately and that the response is JSON.
     *
     * @covers ::__set
     * @covers ::setHandler
     * @covers ::handle
     * @dataProvider handleWithoutArgumentsProvider
     * @param mixed $body
     */
    public function testHandleWithoutArguments($body)
    {
        $expected = array_filter(array_merge(array('id' => 10), (is_array($body) ? $body : array($body))));

        $request = $this->getMock('Slim\Request', array('getBody'));
        $request->expects($this->once())->method('getBody')->willReturn($body);

        $response = $this->getMock('Slim\Response', array('header', 'write'));
        $response->expects($this->once())
            ->method('header')
            ->with($this->equalTo('Content-Type'), $this->equalTo('application/json'));
        $response->expects($this->once())
            ->method('write')
            ->with($this->equalTo(json_encode($expected)));

        $app = $this->getMock('Slim\Slim', array('request', 'response'));
        $app->expects($this->once())->method('request')->willReturn($request);
        $app->expects($this->exactly(2))->method('response')->willReturn($response);

        $route = $this->getMockBuilder('Rephlect\Routes\Route')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $route->app = $app;
        $route->handler = function($data = null) {
            return array_filter(array_merge(array('id' => 10), (is_array($data) ? $data : array($data))));
        };
        $route->handle();
    }

    /**
     * Confirms that invoking the handler with a request parameter will result in calling the route handler
     * appropriately and that the response is JSON.
     *
     * @covers ::__set
     * @covers ::setHandler
     * @covers ::handle
     * @dataProvider handleWithArgumentsProvider
     * @param mixed $id
     * @param mixed $body
     */
    public function testHandleWitArguments($id, $body)
    {
        $expected = array_filter(array_merge(array('id' => $id), (is_array($body) ? $body : array($body))));

        $request = $this->getMock('Slim\Request', array('getBody'));
        $request->expects($this->once())->method('getBody')->willReturn($body);

        $response = $this->getMock('Slim\Response', array('header', 'write'));
        $response->expects($this->once())
            ->method('header')
            ->with($this->equalTo('Content-Type'), $this->equalTo('application/json'));
        $response->expects($this->once())
            ->method('write')
            ->with($this->equalTo(json_encode($expected)));

        $app = $this->getMock('Slim\Slim', array('request', 'response'));
        $app->expects($this->once())->method('request')->willReturn($request);
        $app->expects($this->exactly(2))->method('response')->willReturn($response);

        $route = $this->getMockBuilder('Rephlect\Routes\Route')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $route->app = $app;
        $route->handler = function($arg, $data = null) {
            return array_filter(array_merge(array('id' => $arg), (is_array($data) ? $data : array($data))));
        };
        $route->handle($id);
    }
}
