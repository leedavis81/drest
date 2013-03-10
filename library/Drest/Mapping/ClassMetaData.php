<?php


namespace Drest\Mapping;

/**
 *
 * A class metadata instance that holds all the information for a Drest entity
 * @author Lee
 *
 */
use Drest\DrestException;

class ClassMetaData
{

	const CONTENT_TYPE_SINGLE = 1;
	const CONTENT_TYPE_COLLECTION = 2;


	protected $routes = array();

	protected $writers = array();

	protected $name;

	public function addRoute($name)
	{

	}

	/**
	 * Set a writer instance to be used on this resource
	 * @param object|string $writer - can be either an instance of Drest\Writer\InterfaceWriter of a string (shorthand allowed - Json / Xml) referencing the class.
	 */
	public function addWriter($writer)
	{
		if (!is_object($writer) && is_string($writer))
		{
			throw DrestException::writerMustBeObjectOrString();
		}
		if (is_object($writer))
		{
			if (!$writer instanceof \Drest\Writer\InterfaceWriter)
			{
				throw DrestException::unknownWriterClass(get_class($writer));
			}
			$this->writers[get_class($writer)] = $writer;
		} elseif(is_string($writer))
		{
			$classNamespace = 'Drest\\Writer\\';
			if (class_exists($writer, false))
			{
				$this->writers[$writer] = $writer;
			} elseif (class_exists($classNamespace . $writer))
			{
				$this->writers[$classNamespace . $writer] = $classNamespace . $writer;
			} else
			{
				throw DrestException::unknownWriterClass($writer);
			}
		}
	}

	/**
	 * Sets a unique reference name for the resource. If other resources are created with this name an exception is thrown (must be unique)
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

}









<?php

/*
 * Copyright 2013 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JMS\ObjectRouting\Metadata;

use Metadata\MergeableClassMetadata;

class ClassMetadata extends MergeableClassMetadata
{
    public $routes = array();

    public function addRoute($type, $name, array $params = array())
    {
        $this->routes[$type] = array(
            'name' => $name,
            'params' => $params,
        );
    }
}