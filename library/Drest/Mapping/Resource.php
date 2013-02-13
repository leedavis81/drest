<?php

namespace Drest\Mapping;

/**
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 */
final class Resource implements Annotation
{
    /** @var string */
    public $name;
    /** @var array */
    public $writers = array();
}
