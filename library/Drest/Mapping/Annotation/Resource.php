<?php

namespace Drest\Mapping\Annotation;


/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Resource
{
    /** @var array */
    public $routes;

    /** @var array */
    public $writers;
}
