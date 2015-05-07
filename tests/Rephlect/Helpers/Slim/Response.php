<?php
namespace Tests\Rephlect\Helpers\Slim;

/**
 * Mocks the Slim response class.
 */
class Response
{
    protected $headers = array();

    public function headers($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function write($value)
    {
    }
}
