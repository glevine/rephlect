<?php
namespace Rephlect\Annotations;

/**
 * Annotation class for @Route().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Route extends AbstractAnnotation
{
    protected $path;
    protected $verb = 'get';

    public function __construct(array $options)
    {
        if (isset($options['value'])) {
            $options['path'] = $options['value'];
            unset($options['value']);
        }

        parent::__construct($options);
    }

    public function __set($key, $value)
    {
        $this->assertProperty($key);
        $this->$key = $value;
    }
}
