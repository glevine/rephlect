<?php
namespace Tests\Rephlect\Helpers;

class Routes
{
    /**
     * Returns a mock route that does not call the original constructor.
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @param string $path
     * @param string $verb
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockRoute(\PHPUnit_Framework_TestCase $test, $path, $verb)
    {
        $route = $test->getMockBuilder('Rephlect\Routes\Route')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $route->path = $path;
        $route->verb = $verb;
        return $route;
    }
}
