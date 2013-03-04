<?php

namespace Drest\Mapping\Annotation;

use Drest\Mapping\Annotation\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Resource implements Annotation
{
    /** @var string */
    public $name;
    /** @var boolean */
    public $content;
	/** @var object */
    public $route;
    /** @var array */
    public $writers = array();
}
