<?php
namespace Rephlect\Routes;

class Route
{
    protected $app;
    protected $path = '/';
    protected $handler;
    protected $verb = 'get';

    public function __construct($path, callable $handler)
    {
        $this->__set('path', $path);
        $this->__set('handler', $handler);
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

    public function handle()
    {
        $args = func_get_args();

        switch ($this->verb) {
            case 'post':
            case 'put':
                $json = $this->app->request()->getBody();
                $data = json_decode($json, true);
                $args[] = $data;
                break;
        }

        $response = call_user_func_array($this->handler, $args);

        $this->app->response()->header('Content-Type', 'application/json');
        echo json_encode($response);
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
