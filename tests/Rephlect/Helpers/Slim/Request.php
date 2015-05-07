<?php
namespace Tests\Rephlect\Helpers\Slim;

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
