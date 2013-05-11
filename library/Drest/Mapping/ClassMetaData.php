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
     * An array of RouteMetaData objects defined on this entity
     * @var array $routes
     */
	protected $routes = array();

	/**
	 * An array of Drest\Writer\InterfaceWriter object defined on this entity
	 * @var array $writers
	 */
	protected $writers = array();

	/**
	 * Name of the class that we collected metadata for (eg Entities\User)
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
	 * Add a route metadata object
	 * @param Drest\Mapping\RouteMetaData $route
	 */
	public function addRouteMetaData(RouteMetaData $route)
	{
	    $route->setClassMetaData($this);
        $this->routes[$route->getName()] = $route;
	}

	/**
	 * Get either and array of all route metadata information, or an entry by name. Returns false if entry cannot be found
	 * @return mixed $routes;
	 */
	public function getRoutesMetaData($name = null)
	{
	    if ($name === null)
	    {
	        return $this->routes;
	    }
	    if (isset($this->routes[$name]))
	    {
	        return $this->routes[$name];
	    }
	    return false;
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
			$this->writers[] = $writer;
		} elseif(is_string($writer))
		{
		    $this->writers[] = $writer;
		} else
		{
		    throw DrestException::writerMustBeObjectOrString();
		}
	}

	/**
	 * Get the writers available on this resource
	 * @return array writers can be strings or an already instantiated object
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
	 * Get the text name that represents a single element. eg: user
	 * @return string $element_name
	 */
	public function getElementName()
	{
        // attempt to pull an entity name from the class
	    $classNameParts = explode('\\', $this->className);
	    if (is_array($classNameParts))
	    {
            return strtolower(\Drest\Inflector::singularize(array_pop($classNameParts)));
	    }
	}

	/**
	 * Get an alias for this entity - used for DQL / QueryBuilder
	 * @return string alias unique string representing this entity
	 */
	public function getEntityAlias()
	{
        return strtolower(preg_replace("/[^a-zA-Z0-9_\s]/", "", $this->getClassName()));
	}

	/**
	 * Get a plural term for the element name
	 * @return string $collection_name
	 */
	public function getCollectionName()
	{
	    $elementName = $this->getElementName();
	    return \Drest\Inflector::pluralize($elementName);
	}

	/**
	 * Serialise this object
	 * @return array
	 */
    public function serialize()
    {
        return serialize(array(
            $this->routes,
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
            $this->routes,
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
