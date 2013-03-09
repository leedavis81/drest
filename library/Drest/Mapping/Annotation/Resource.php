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
    public $name;
//    /** @var boolean */
//    public $content;
//	/** @var object */
//    public $route;
//    /** @var array */
//    public $writers = array();
}
