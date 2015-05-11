<?php
namespace Slim;

/**
 * Mocks the Slim application class.
 */
class Slim
{
    public $request;
    public $response;

    public function request()
    {
        return $this->request;
    }

    public function response()
    {
        return $this->response;
    }
}
