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
    public $route_pattern;

    /** @var array */
    public $route_conditions;

    /** @var array */
    public $service_call;

    /** @var array */
    public $verbs;

    /** @var array */
    public $expose;

    /** @var boolean */
    public $allow_options;
}
