<?php

namespace Drest\Mapping\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
final class Route
{
    /** @var string */
    public $name;

    /** @var string */
    public $content;

    /** @var string */
    public $routePattern;

    /** @var array */
    public $routeConditions;

    /** @var string */
    public $action;

    /** @var array */
    public $verbs;

    /** @var array */
    public $expose;

    /** @var boolean */
    public $allowOptions;

    /** @var boolean */
    public $collection;

    /** @var boolean */
    public $origin;
}
