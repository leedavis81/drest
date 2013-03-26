<?php

namespace Drest\Mapping\Annotation;


/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Resource
{
    /** @var array */
    public $services = array();
}
