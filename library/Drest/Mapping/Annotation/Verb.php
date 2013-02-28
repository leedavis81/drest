<?php

namespace Drest\Mapping\Annotation;

use Drest\Mapping\Annotation\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 */
final class Verb implements Annotation
{
    /** @var integer */
    public $name;
	/** @var string */
    public $function;
}
