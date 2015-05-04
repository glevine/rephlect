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
     * @var string
     * The HTTP verb (method) that applies to the route.
     */
    protected $verb = 'get';

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
}
