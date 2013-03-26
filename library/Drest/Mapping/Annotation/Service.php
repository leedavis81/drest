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

    /** @var array */
    public $writers = array();

	/** @var Drest\Mapping\Annotation\Route */
    public $route;
}
