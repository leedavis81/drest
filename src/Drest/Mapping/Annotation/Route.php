<?php
/**
 * This file is part of the Drest package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Lee Davis
 * @copyright Copyright (c) Lee Davis <@leedavis81>
 * @link https://github.com/leedavis81/drest/blob/master/LICENSE
 * @license http://opensource.org/licenses/MIT The MIT X License (MIT)
 */
namespace Drest\Mapping\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
final class Route implements \ArrayAccess
{

    /** @var string */
    public $name;

    /** @var string */
    public $content;

    /** @var string */
    public $routePattern;

    /** @var array */
    public $routeConditions;

    /** @var string */
    public $action;

    /** @var array */
    public $verbs;

    /** @var array */
    public $expose;

    /** @var boolean */
    public $allowOptions;

    /** @var boolean */
    public $collection;

    /** @var boolean */
    public $origin;

    /**
     * Return key exists.
     *
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    /**
     * Return the value for the offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->$offset : null;
    }

    /**
     * Set the offset
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    /**
     * Unset the offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        if($this->offsetExists($offset)) {
            unset($this->$offset);
        }
    }
}
