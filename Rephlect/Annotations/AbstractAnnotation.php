<?php
namespace Rephlect\Annotations;

/**
 * Class AbstractAnnotation
 * @package Rephlect\Annotations
 */
abstract class AbstractAnnotation
{
    /**
     * Constructor.
     *
     * @param array $options An array of key/value parameters.
     */
    public function __construct(array $options)
    {
        foreach ($options as $key => $value) {
            $this->__set($key, $value);
        }
    }

    /**
     * Returns the value of the specified property.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->$key;
    }

    /**
     * Sets the value of the specified property.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->$key = $value;
    }
}
