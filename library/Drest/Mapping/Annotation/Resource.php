<?php

namespace Drest\Mapping\Annotation;

use Drest\Mapping\Annotation\Annotation;

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
