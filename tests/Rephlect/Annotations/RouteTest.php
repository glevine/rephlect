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
    public function constructorProvider()
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
     * Data provider the operation to merge two routes.
     *
     * @return array
     */
    public function mergeProvider()
    {
        return array(
            // no defaults, annotation only defines path
            array(
                array(),
                array('path' => '/posts/:id'),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array(),
                ),
            ),
            // no defaults, annotation defines all
            array(
                array(),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('put'),
                    'conditions' => array('id' => '\d+'),
                ),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('put'),
                    'conditions' => array('id' => '\d+'),
                ),
            ),
            // default path
            array(
                array('path' => '/posts'),
                array('path' => '/:id'),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array(),
                ),
            ),
            // default conditions
            array(
                array('conditions' => array('id' => '\d+')),
                array('path' => '/posts/:id'),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array('id' => '\d+'),
                ),
            ),
            // default verb (gets ignored)
            array(
                array('verb' => 'delete'),
                array('path' => '/posts/:id'),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array(),
                ),
            ),
            // selective overwrite in cases of condition conflicts
            array(
                array('conditions' => array('id' => '\d+')),
                array(
                    'path' => '/posts/:id',
                    'conditions' => array('id' => '\d{1,3}'),
                ),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('get'),
                    'conditions' => array('id' => '\d{1,3}'),
                ),
            ),
            // typical scenario
            array(
                array(
                    'path' => '/posts',
                    'conditions' => array(
                        'id' => '\d+',
                        'name' => '[a-zA-Z]',
                    ),
                ),
                array(
                    'path' => '/:id',
                    'verb' => array('put', 'patch'),
                    'conditions' => array('id' => '\d{1,3}'),
                ),
                array(
                    'path' => '/posts/:id',
                    'verb' => array('put', 'patch'),
                    'conditions' => array(
                        'id' => '\d{1,3}',
                        'name' => '[a-zA-Z]',
                    ),
                ),
            ),
        );
    }

    /**
     * @covers ::__construct
     * @covers ::setVerb
     * @covers Rephlect\Annotations\AbstractAnnotation
     * @dataProvider constructorProvider
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

    /**
     * @covers ::__construct
     * @covers ::setVerb
     * @covers ::merge
     * @covers Rephlect\Annotations\AbstractAnnotation
     * @dataProvider mergeProvider
     * @param $defaults
     * @param $annotation
     * @param $expected
     */
    public function testMerge($defaults, $annotation, $expected)
    {
        $default = new Route($defaults);
        $method = new Route($annotation);
        $merged = $default->merge($method);
        $this->assertSame($expected['path'], $merged->path);
        $this->assertSame($expected['verb'], $merged->verb);
        $this->assertSame($expected['conditions'], $merged->conditions);
    }
}
