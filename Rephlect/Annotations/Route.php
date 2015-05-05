<?php
namespace Rephlect\Annotations;

/**
 * Class Route
 * @package Rephlect\Annotations
 *
 * Annotation class for @Route().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Route extends AbstractAnnotation
{
    /**
     * @var string
     * The route's path (e.g., "/Foo/:id").
     */
    protected $path;

    /**
     * @var array
     * The HTTP verbs (methods) that apply to the route.
     */
    protected $verb = array('get');

    /**
     * {@inheritDoc}
     */
    public function __construct(array $options)
    {
        if (isset($options['value'])) {
            $options['path'] = $options['value'];
            unset($options['value']);
        }

        parent::__construct($options);
    }

    /**
     * Sets the verb.
     *
     * In support of multiple verbs per route, the verb should always be an array, even when there is only one.
     *
     * @param string|array $verb
     */
    protected function setVerb($verb)
    {
        if (!is_array($verb)) {
            $verb = array($verb);
        }

        $this->verb = $verb;
    }
}
