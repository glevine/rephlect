<?php
namespace Slim;

/**
 * Mocks the Slim application class.
 */
class Slim
{
    public $request;
    public $response;

    public function hook($name, callable $handler)
    {
    }

    public function request()
    {
        return $this->request;
    }

    public function response()
    {
        return $this->response;
    }

    public function map()
    {
    }

    public function get()
    {
    }

    public function post()
    {
    }

    public function put()
    {
    }

    public function patch()
    {
    }

    public function delete()
    {
    }

    public function options()
    {
    }

    public function any()
    {
    }
}
