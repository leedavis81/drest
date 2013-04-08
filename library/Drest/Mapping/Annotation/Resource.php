<?php

namespace Drest\Mapping\Annotation;


/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Resource
{
    /** @var array */
    public $services;

    /** @var array */
    public $writers;
}
