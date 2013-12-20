<?php
namespace Drest\Mapping;

use Doctrine\ORM\EntityManager;
use Drest\Service\Action\AbstractAction;
use DrestCommon\Representation\AbstractRepresentation;
use DrestCommon\Representation\RepresentationException;
use Doctrine\Common\Inflector\Inflector;

/**
 *
 * A class metadata instance that holds all the information for a Drest entity
 * @author Lee
 *
 */
class ClassMetaData implements \Serializable
{

    /**
     * An array of RouteMetaData objects defined on this entity
     * @var array $routes
     */
    protected $routes = array();

    /**
     * An array of \DrestCommon\Representation\AbstractRepresentation object defined on this entity
     * @var array $representations
     */
    protected $representations = array();

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
     * The origin route name - null if one isn't found
     * @vat string $originRouteName
     */
    public $originRouteName;


    /**
     * Construct an instance of this classes metadata
     * @param \ReflectionClass $classRefl
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
     * @param RouteMetaData $route
     */
    public function addRouteMetaData(RouteMetaData $route)
    {
        $route->setClassMetaData($this);
        $this->routes[$route->getName()] = $route;
    }

    /**
     * Get either and array of all route metadata information, or an entry by name. Returns false if entry cannot be found
     * @param null $name
     * @return RouteMetaData|array $routes
     */
    public function getRoutesMetaData($name = null)
    {
        if ($name === null) {
            return $this->routes;
        }
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }
        return false;
    }

    /**
     * get the origin route (if one is available), otherwise attempts to determine it from a GET {path}/{primary_key} route
     * @param EntityManager $em - Optionally pass the entity manager to assist in determining a GET origin location
     * @return null|RouteMetaData $route
     */
    public function getOriginRoute(EntityManager $em = null)
    {
        if (!empty($this->originRouteName)) {
            if (($route = $this->getRoutesMetaData($this->originRouteName)) !== false) {
                return $route;
            }
        } elseif (!is_null($em))
        {
            $ormClassMetadata = $em->getClassMetadata($this->getClassName());

            foreach ($this->getRoutesMetaData() as $route) {
                /* @var RouteMetaData $route */
                if (in_array('GET', $route->getVerbs()) && preg_match('/^(.*)?\/:' . implode('/:', $ormClassMetadata->getIdentifierFieldNames()) . '$/', $route->getRoutePattern())) {
                    $this->originRouteName = $route->getName();
                    return $route;
                }
            }
        }
        return null;
    }

    /**
     * Add an array of representations
     * @param array $representations
     */
    public function addRepresentations(array $representations)
    {
        foreach ($representations as $representation) {
            $this->addRepresentation($representation);
        }
    }

    /**
     * Set a representation instance to be used on this resource
     * @param object|string $representation - can be either an instance of DrestCommon\Representation\AbstractRepresentation or a string (shorthand allowed - Json / Xml) referencing the class.
     * @throws RepresentationException
     */
    public function addRepresentation($representation)
    {
        if (is_object($representation)) {
            if (!$representation instanceof AbstractRepresentation) {
                throw RepresentationException::unknownRepresentationClass(get_class($representation));
            }
            $this->representations[] = $representation;
        } elseif (is_string($representation)) {
            $this->representations[] = $representation;
        } else {
            throw RepresentationException::representationMustBeObjectOrString();
        }
    }

    /**
     * Get the representations available on this resource
     * @return array representations can be strings or an already instantiated object
     */
    public function getRepresentations()
    {
        return $this->representations;
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
        if (is_array($classNameParts)) {
            return strtolower(Inflector::singularize(array_pop($classNameParts)));
        }
        return $this->className;
    }

    /**
     * Get an alias for this entity - used for DQL / QueryBuilder
     * @param string $fieldName - The field the relation is on. Default to root when using top level.
     * @return string alias unique string representing this entity
     */
    public function getEntityAlias($fieldName = 'rt')
    {
        return AbstractAction::getAlias($this->getClassName(), $fieldName);
    }

    /**
     * Get a plural term for the element name
     * @return string $collection_name
     */
    public function getCollectionName()
    {
        $elementName = $this->getElementName();
        return Inflector::pluralize($elementName);
    }

    /**
     * Serialise this object
     * @return array
     */
    public function serialize()
    {
        return serialize(array(
            $this->routes,
            $this->representations,
            $this->className,
            $this->filePath,
            $this->createdAt,
            $this->originRouteName
        ));
    }

    /**
     * Un-serialise this object and reestablish it's state
     */
    public function unserialize($string)
    {
        list(
            $this->routes,
            $this->representations,
            $this->className,
            $this->filePath,
            $this->createdAt,
            $this->originRouteName
            ) = unserialize($string);

        foreach ($this->routes as $route)
        {
            /* @var $route RouteMetaData */
            $route->setClassMetaData($this);
        }
        $this->reflection = new \ReflectionClass($this->className);
    }

    /**
     * Check to see if this classes metadata has expired (file has been modified or deleted)
     * @param timestamp
     * @return bool
     */
    public function expired($timestamp = null)
    {
        if ($timestamp === null) {
            $timestamp = $this->createdAt;
        }

        if (!file_exists($this->filePath) || $timestamp < filemtime($this->filePath)) {
            return true;
        }
        return false;
    }
}
