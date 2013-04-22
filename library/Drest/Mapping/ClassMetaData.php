<?php

namespace Drest\Mapping;

/**
 *
 * A class metadata instance that holds all the information for a Drest entity
 * @author Lee
 *
 */
use Drest\DrestException;

class ClassMetaData implements \Serializable
{

    /**
     * An array of ServiceMetaData objects defined on this entity
     * @var array $services
     */
	protected $services = array();

	/**
	 * The repository classname used on the ORM Entity definition
	 * @var string $respositoryClass
	 */
	protected $repositoryClass;

	/**
	 * An array of Drest\Writer\InterfaceWriter object defined on this entity
	 * @var array $writers
	 */
	protected $writers = array();

	/**
	 * Name of the class that we collected metadata for
	 * @var string $className
	 */
	protected $className;

	/**
	 * A reflection of the class
	 * @var \ReflectionClass $reflection
	 */
	protected $reflection;

	/**
	 * File path used to load this metadata
	 * @var string $fileResources
	 */
    public $filePath;

    /**
     * time this instance was created - current Unix timestamp
     * @var integer $createdAt
     */
    public $createdAt;


	/**
	 * Construct an instance of this classes metadata
	 * @param \ReflectionClass $className
	 */
    public function __construct(\ReflectionClass $classRefl)
    {
        $this->reflection = $classRefl;
        $this->className = $classRefl->name;

        $this->filePath = $classRefl->getFileName();
        $this->createdAt = time();
    }

	/**
	 * Add a service metadata object
	 * @param Drest\Mapping\ServiceMetaData $service
	 */
	public function addServiceMetaData(ServiceMetaData $service)
	{
	    $service->setClassMetaData($this);
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
	 * Set the repository class name
	 * @param string $className
	 */
	public function setRepositoryClass($className)
	{
        $this->repositoryClass = $className;
	}

	/**
	 * Get the repository class used on the ORM annotations
	 * @return string $className
	 */
	public function getRepositoryClass()
	{
	    return $this->repositoryClass;
	}

	/**
	 * Add an array of writers
	 * @param array $writers
	 */
	public function addWriters(array $writers)
	{
	    foreach ($writers as $writer)
	    {
	        $this->addWriter($writer);
	    }
	}

	/**
	 * Set a writer instance to be used on this resource
	 * @param object|string $writer - can be either an instance of Drest\Writer\InterfaceWriter of a string (shorthand allowed - Json / Xml) referencing the class.
	 */
	public function addWriter($writer)
	{
		if (is_object($writer))
		{
			if (!$writer instanceof \Drest\Writer\InterfaceWriter)
			{
				throw DrestException::unknownWriterClass(get_class($writer));
			}
			$this->writers[get_class($writer)] = $writer;
		} elseif(is_string($writer))
		{
			$namespacedClass = 'Drest\\Writer\\' . $writer;
			if (class_exists($writer, false))
			{
				$this->writers[$writer] = new $writer();
			} elseif (class_exists($namespacedClass))
			{
				$this->writers[$namespacedClass] = new $namespacedClass();
			} else
			{
				throw DrestException::unknownWriterClass($writer);
			}
		} else
		{
		    throw DrestException::writerMustBeObjectOrString();
		}
	}

	/**
	 * Get the writers available on this resource
	 */
	public function getWriters()
	{
	    return $this->writers;
	}

	/**
	 * Get the metadata class name (immutable)
	 * @return string $className
	 */
	public function getClassName()
	{
	    return $this->className;
	}

	/**
	 * Serialise this object
	 * @return array
	 */
    public function serialize()
    {
        return serialize(array(
            $this->services,
            $this->repositoryClass,
            $this->writers,
            $this->className,
            $this->filePath,
            $this->createdAt
        ));
    }

    /**
     * Unserialise this object and reestablish it's state
     */
    public function unserialize($string)
    {
        list(
            $this->services,
            $this->repositoryClass,
            $this->writers,
            $this->className,
            $this->filePath,
            $this->createdAt
        ) = unserialize($string);

        $this->reflection = new \ReflectionClass($this->className);
    }

    /**
     * Check to see if this classes metadata has expired (file has been modified or deleted)
     * @param timestamp
     */
    public function expired($timestamp = null)
    {
        if ($timestamp === null)
        {
            $timestamp = $this->createdAt;
        }

        if (!file_exists($this->filePath) || $timestamp < filemtime($this->filePath))
        {
            return true;
        }

        return false;
    }
}
