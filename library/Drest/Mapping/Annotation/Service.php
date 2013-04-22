<?php

namespace Drest\Mapping\Annotation;


/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Service
{
    /** @var string */
    public $name;

    /** @var string */
    public $content;

    /** @var string */
    public $route_pattern;

    /** @var array */
    public $route_conditions;

    /** @var string */
    public $repository_method;

    /** @var array */
    public $verbs;

    /** @var array */
    public $expose;
}
