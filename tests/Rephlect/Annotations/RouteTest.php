<?php
namespace Tests\Rephlect\Annotations;

use Rephlect\Annotations\Route;

/**
 * @coversDefaultClass Rephlect\Annotations\Route
 */
class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for the Route constructor.
     *
     * @return array
     */
    public function provider()
    {
        return array(
            // value becomes path
            array(
                array(
                    'value' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array(),
                ),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array(),
                ),
            ),
            // verb is not "get"
            array(
                array(
                    'value' => '/posts/',
                    'verb' => array('post'),
                    'conditions' => array(),
                ),
                array(
                    'path' => '/posts/',
                    'verb' => array('post'),
                    'conditions' => array(),
                ),
            ),
            // verb is a string... becomes an array
            array(
                array(
                    'value' => '/posts/',
                    'verb' => 'post',
                    'conditions' => array(),
                ),
                array(
                    'path' => '/posts/',
                    'verb' => array('post'),
                    'conditions' => array(),
                ),
            ),
            // path is defined
            array(
                array(
                    'path' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array(),
                ),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array(),
                ),
            ),
            // more than one verb
            array(
                array(
                    'value' => '/posts/:id',
                    'verb' => array('put', 'patch'),
                    'conditions' => array(),
                ),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('put', 'patch'),
                    'conditions' => array(),
                ),
            ),
            // the verb is not specified
            array(
                array(
                    'value' => '/posts/:id',
                    'conditions' => array(),
                ),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array(),
                ),
            ),
            // the conditions are not specified
            array(
                array(
                    'value' => '/posts/:id',
                    'verb' => ('get'),
                ),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array(),
                ),
            ),
            // with parameter conditions
            array(
                array(
                    'value' => '/posts/:id',
                    'verb' => ('get'),
                    'conditions' => array('id' => '\d+'),
                ),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array('id' => '\d+'),
                ),
            ),
        );
    }

    /**
     * @covers ::__construct
     * @covers ::setVerb
     * @covers Rephlect\Annotations\AbstractAnnotation
     * @dataProvider provider
     * @param array $options
     * @param array $expected
     */
    public function testRoute($options, $expected)
    {
        $route = new Route($options);

        foreach ($expected as $key => $value) {
            $this->assertSame($expected[$key], $route->$key);
        }
    }
}
