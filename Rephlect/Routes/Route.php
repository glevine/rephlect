<?php
namespace Rephlect\Routes;

/**
 * Class Route
 * @package Rephlect\Routes
 */
class Route
{
    /**
     * @var \Slim\Slim
     * The application to which the route belongs.
     */
    protected $app;

    /**
     * @var string
     * The route's path.
     */
    protected $path;

    /**
     * @var callable
     * The method that is called to handle the route.
     */
    protected $handler;

    /**
     * @var string
     * The HTTP verb (method) that applies to the route.
     */
    protected $verb = 'get';

    /**
     * @var array
     * The route's conditions.
     */
    protected $conditions = array();

    /**
     * Constructor.
     *
     * @param callable $handler
     * @param string $path
     */
    public function __construct(callable $handler, $path = '/')
    {
        $this->__set('handler', $handler);
        $this->__set('path', $path);
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

    /**
     * Invokes the route handler.
     *
     * Any parameters are passed to the invoked function. Any data parsed from the request body is appended, as a hash,
     * to the end of the parameter list.
     *
     * Sets the response's Content-Type header to "application/json" and appends the JSON encoded response.
     */
    public function handle()
    {
        $args = func_get_args();
        $body = $this->app->request()->getBody();

        if (!empty($body)) {
            $args[] = $body;
        }

        $response = call_user_func_array($this->handler, $args);

        $this->app->response()->header('Content-Type', 'application/json');
        $this->app->response()->write(json_encode($response));
    }

    /**
     * Sets the handler.
     *
     * This method only exists as a means to guarantee that the handler is a callable.
     *
     * @param callable $handler
     */
    protected function setHandler(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Sets the path.
     *
     * A path must start with a slash and must not have multiple slashes at the beginning because the generated path
     * for this route would be confused with a network path (e.g. "//domain.com/path").
     *
     * @param string $path
     */
    protected function setPath($path)
    {
        $this->path = '/' . ltrim(trim($path), '/');
    }

    /**
     * Sets the verb.
     *
     * Defaults to "get" when empty.
     *
     * @param string $verb
     * @throws \InvalidArgumentException
     */
    protected function setVerb($verb)
    {
        if (!is_string($verb) && !is_null($verb)) {
            throw new \InvalidArgumentException('Invalid HTTP verb.');
        }

        $verb = trim($verb);

        if (empty($verb)) {
            $verb = 'get';
        }

        $this->verb = $verb;
    }
}
