<?php
namespace Rephlect\Routes;

class Route
{
    protected $path = '/';

    public function __construct($path)
    {
        $this->__set('path', $path);
    }

    public function __get($key)
    {
        $this->assertProperty($key);
        return $this->$key;
    }

    public function __set($key, $value)
    {
        $this->assertProperty($key);

        switch ($key) {
            case 'path':
                $this->setPath($value);
                break;
            default:
                $this->$key = $value;
        }
    }

    /**
     * A pattern must start with a slash and must not have multiple slashes at the beginning because the generated path
     * for this route would be confused with a network path, e.g. '//domain.com/path'.
     *
     * @param string $pattern
     */
    protected function setPath($pattern)
    {
        $this->path = '/' . ltrim(trim($pattern), '/');
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
