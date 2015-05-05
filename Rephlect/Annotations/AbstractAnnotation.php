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
        $getter = "get{$key}";

        if (method_exists($this, $getter)) {
            return $this->$getter();
        } else {
            return $this->$key;
        }
    }

    /**
     * Sets the value of the specified property.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $setter = "set{$key}";

        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            $this->$key = $value;
        }
    }
}
