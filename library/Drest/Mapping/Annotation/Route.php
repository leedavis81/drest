<?php

namespace Drest\Mapping\Annotation;

use Drest\Mapping\Annotation\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Route implements Annotation
{
    /** @var string */
    public $name;
    /** @var string */
    public $path;
    /** @var array */
    public $verbs;
}
