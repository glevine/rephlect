<?php
namespace Slim;

/**
 * Mocks the Slim request class.
 */
class Request
{
    protected $body;

    public function getBody()
    {
        return $this->body;
    }
}
