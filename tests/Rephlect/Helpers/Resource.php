<?php
namespace Tests\Rephlect\Helpers;

/**
 * @Route("/posts")
 */
class Resource
{
    /**
     * @Route("/", verb="post")
     * @param array $data
     */
    public function create($data = array())
    {
    }

    /**
     * @Route("/:id", conditions={"id"="\d+"})
     * @param $id
     */
    public function read($id)
    {
    }

    /**
     * @Route("/:id", verb={"put", "patch"}, conditions={"id"="\d+"})
     * @param $id
     * @param array $data
     */
    public function update($id, $data = array())
    {
    }

    /**
     * @Route("/:id", verb="delete")
     * @param $id
     */
    public function delete($id)
    {
    }
}
