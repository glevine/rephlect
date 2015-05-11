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
     * @var array
     * The route's conditions.
     */
    protected $conditions = array();

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
     * {@inheritDoc}
     *
     * Returns a new annotation object without modifying this one.
     *
     * @param Route $annotation
     * @return Route
     */
    public function merge($annotation)
    {
        $className = get_class($this);
        $options = array(
            // append the path from the method annotation to that from the class annotation to get the full path
            'path' => $this->path . $annotation->path,
            // the verb is never defined on the class annotation, so simply use the verb from the method annotation
            'verb' => $annotation->verb,
            // merge the conditions from the method annotation with those from the class annotation
            'conditions' => array_unique(array_merge($this->conditions, $annotation->conditions)),
        );

        return new $className($options);
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
