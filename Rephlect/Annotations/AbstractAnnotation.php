<?php
namespace Rephlect\Annotations;

abstract class AbstractAnnotation
{
    /**
     * Constructor.
     *
     * @param array $options An array of key/value parameters.
     * @throws \BadMethodCallException
     */
    public function __construct(array $options)
    {
        foreach ($options as $key => $value) {
            $this->__set($key, $value);
        }
    }

    /**
     * @param $key
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __get($key)
    {
        $this->assertProperty($key);
        return $this->$key;
    }

    /**
     * @param $key
     * @param $value
     * @throws \BadMethodCallException
     */
    public function __set($key, $value)
    {
        $this->assertProperty($key);
        $this->$key = $value;
    }

    /**
     * @param $key
     * @throws \BadMethodCallException
     */
    protected function assertProperty($key)
    {
        if (!property_exists($this, $key)) {
            throw new \BadMethodCallException(
                sprintf("Unknown property '%s' on annotation '%s'.", $key, get_class($this))
            );
        }
    }
}
