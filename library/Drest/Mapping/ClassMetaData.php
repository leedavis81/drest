<?php

namespace Drest\Mapping;

/**
 *
 * A class metadata instance that holds all the information for a Drest entity
 * @author Lee
 *
 */
use Drest\DrestException,
    Metadata\MergeableClassMetadata;

class ClassMetaData extends MergeableClassMetadata
{

    /**
     * An array of ServiceMetaData objects defined on this entity
     * @var array $services
     */
	protected $services = array();

	/**
	 * An array of Drest\Writer\InterfaceWriter object defined on this entity
	 * @var array $writers
	 */
	protected $writers = array();

	/**
	 * Add a service metadata object
	 * @param Drest\Mapping\ServiceMetaData $service
	 */
	public function addServiceMetaData(ServiceMetaData $service)
	{
        $this->services[$service->getName()] = $service;
	}

	/**
	 * Get either and array of all service metadata information, or an entry by name. Returns false if entry cannot be found
	 * @return mixed $services;
	 */
	public function getServicesMetaData($name = null)
	{
	    if ($name === null)
	    {
	        return $this->services;
	    }
	    if (isset($this->services[$name]))
	    {
	        return $this->services[$name];
	    }
	    return false;
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
	 * Get the writers available on this resource
	 */
	public function getWriters()
	{
	    return $this->writers;
	}

}
