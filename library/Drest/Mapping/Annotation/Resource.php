<?php

namespace Drest\Mapping\Annotation;

//* @Drest\Resource {name="user", route="{path='/user/{id}'}", content="single", writers={'xml', 'json'}}

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Resource
{
    /** @var string */
    public $content;

	/** @var Drest\Mapping\Annotation\Route */
    public $route;

    /** @var array */
    public $writers = array();
}
