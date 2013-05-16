<?php
namespace Drest\Mapping\Annotation;


/**
 * @Annotation
 * @Target("ALL")
 */
final class Handle
{
    /** @var string */
    public $for;
}
