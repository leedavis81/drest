<?php


namespace Drest\Mapping\Driver;


use Drest\Mapping\RouteMetaData;

use Drest\DrestException;

use	Doctrine\Common\Annotations,
    Doctrine\Common\Persistence\Mapping\Driver as PersistenceDriver,
    Drest\Mapping\Driver\DriverInterface,
	Drest\Mapping,
	Drest\Mapping\Annotation;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 * Doesn't require paths / file extensions as entities are pull from the doctrine entity manager
 */
class AnnotationDriver implements DriverInterface
{

	/**
	 * Annotations reader
	 * @var Doctrine\Common\Annotations\AnnotationReader $reader
	 */
    private $reader;

    /**
     * The paths to look for mapping files - immutable as classNames as cached, must be passed on construct.
     * @var array
     */
    protected $paths;

    /**
     * Loaded classnames
     * @var array
     */
    protected $classNames = array();

    /**
     * Extensions of the files to read
     * @var array
     */
    protected $extensions = array();


	public function __construct(Annotations\AnnotationReader $reader, $paths = array())
    {
        $this->reader = $reader;
        $this->paths = $paths;

        $this->addExtension('php');
    }

    /**
     * Get paths to annotation classes
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Add an extension to look for classes
     * @param mixed $extension - can be a string or an array of extensions
     */
    public function addExtension($extension)
    {
        $extension = (array) $extension;
        foreach ($extension as $ext)
        {
            if (!in_array($ext, $this->extensions))
            {
                $this->extensions[] = strtolower(preg_replace("/[^a-zA-Z0-9.\s]/", "", $ext));
            }
        }
    }

    /**
     * Remove all registered extensions, if an extension name is passed, only remove that entry
     * @param string $extension
     */
    public function removeExtensions($extension = null)
    {
        if (is_null($extension))
        {
            $this->extensions = array();
        } else
        {
            $offset = array_search($extension, $this->extensions);
            if ($offset !== false)
            {
                unset($this->extensions[$offset]);
            }
        }
    }


    /**
     * Get all the metadata class names known to this driver.
     * @return array $classes
     */
    public function getAllClassNames()
    {
        if (empty($this->classNames))
        {
            if (empty($this->paths))
            {
                throw DrestException::pathToConfigFilesRequired();
            }
            $classes = array();
            $included = array();
            foreach ($this->paths as $path)
            {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($iterator as $file)
                {
                    if (!in_array($file->getExtension(), $this->extensions))
                    {
                        continue;
                    }

                    $path = $file->getRealPath();
                    require_once $path;
                    // Register the files we've included here
                    $included[] = $path;
                }
            }

            foreach (get_declared_classes() as $className)
            {
                $reflClass = new \ReflectionClass($className);
                $sourceFile = $reflClass->getFileName();
                if (in_array($sourceFile, $included) && $this->isDrestResource($className))
                {
                    $classes[] = $className;
                }
            }

            $this->classNames = $classes;
        }

        return $this->classNames;
    }

    /**
     * Does the class contain a drest resource object
     * @param string $className
     */
    public function isDrestResource($className)
    {
        $classAnnotations = $this->reader->getClassAnnotations(new \ReflectionClass($className));

        foreach ($classAnnotations as $classAnnotation)
        {
            if ($classAnnotation instanceof Annotation\Resource)
            {
                return true;
            }
        }
        return false;
    }


	/**
	 * Load metadata for a class name
	 * @param object|string $className - Pass in either the class name, or an instance of that class
	 * @return Drest\Mapping\ClassMetaData $metaData - return null if metadata couldn't be populated from annotations
	 */
    public function loadMetadataForClass($class)
    {
        $resourceFound = false;

        if (is_string($class))
        {
            $class = new \ReflectionClass($class);
        }

        $metadata = new Mapping\ClassMetadata($class);
        foreach ($this->reader->getClassAnnotations($class) as $annotatedObject)
        {
        	if ($annotatedObject instanceof Annotation\Resource)
        	{
        	    $resourceFound = true;
        	    $originFound = false;

        	    if ($annotatedObject->routes === null)
        	    {
        	        throw DrestException::annotatedResourceRequiresAtLeastOneServiceDefinition($class->name);
        	    }

        	    $metadata->addRepresentations($annotatedObject->representations);

        	    foreach ($annotatedObject->routes as $route)
        	    {
        	        $routeMetaData = new Mapping\RouteMetaData();

        	        // Set name
        	        $route->name = preg_replace("/[^a-zA-Z0-9_\s]/", "", $route->name);
        	        if ($route->name == '')
        	        {
                        throw DrestException::routeNameIsEmpty();
        	        }
        	        if ($metadata->getRoutesMetaData($route->name) !== false)
        	        {
        	            throw DrestException::routeAlreadyDefinedWithName($class->name, $route->name);
        	        }
                    $routeMetaData->setName($route->name);

                    // Set verbs (will throw if invalid)
        	        if (isset($route->verbs))
        	        {
        	            $routeMetaData->setVerbs($route->verbs);
        	        }

        	        if (isset($route->collection))
        	        {
        	            $routeMetaData->setCollection($route->collection);
        	        }

        	        // Add the route pattern
        	        $routeMetaData->setRoutePattern($route->routePattern);

        	        if (is_array($route->routeConditions))
        	        {
                        $routeMetaData->setRouteConditions($route->routeConditions);
        	        }

        	        // Set the exposure array
        	        if (is_array($route->expose))
        	        {
        	            $routeMetaData->setExpose($route->expose);
        	        }

        	        // Set the allow options value
        	        if (isset($route->allowOptions))
        	        {
        	            $routeMetaData->setAllowedOptionRequest($route->allowOptions);
        	        }

        	        // Add action class
        	        if (isset($route->action))
        	        {
        	            $routeMetaData->setActionClass($route->action);
        	        }

        	        // If the origin flag is set, set the name on the classmetadata
        	        if (!is_null($route->origin))
        	        {
        	            if ($originFound)
        	            {
        	                throw DrestException::resourceCanOnlyHaveOneRouteSetAsOrigin();
        	            }
        	            $metadata->originRouteName = $route->name;
        	            $originFound = true;
        	        }

                    $metadata->addRouteMetaData($routeMetaData);
        	    }

                // Attempt to determine the origin by using pattern GET {optional_string}/{primary_key}
                if (!$originFound)
                {
                    foreach ($metadata->getRoutesMetaData() as $route)
                    {
                        //@todo: possibly improve this by using the actual primary key (taken from $em metadata)
                        if (in_array('GET', $route->getVerbs()) && preg_match('/^(.*)?\/:id$/', $route->getRoutePattern()))
                        {
                            $metadata->originRouteName = $route->getName();
                            break;
                        }
                    }
                }

        	    // Set the handle calls
                foreach ($class->getMethods() as $method)
                {
                    if ($method->isPublic())
                    {
                        foreach ($this->reader->getMethodAnnotations($method) as $methodAnnotation)
                        {
                            if ($methodAnnotation instanceof Annotation\Handle)
                            {
                                // Make sure the for is not empty
                                if (empty($methodAnnotation->for) || !is_string($methodAnnotation->for))
                                {
                                    throw DrestException::handleForCannotBeEmpty();
                                }
                                if (($routeMetaData = $metadata->getRoutesMetaData($methodAnnotation->for)) === false)
                                {
                                    throw DrestException::handleAnnotationDoesntMatchRouteName($methodAnnotation->for);
                                }
                                if ($routeMetaData->hasHandleCall())
                                {
                                    // There is already a handle set for this route
                                    throw DrestException::alreadyHandleDefinedForRoute($routeMetaData);
                                }
                                $routeMetaData->setHandleCall($method->getName());
                            }
                        }
                    }
                }

                // Error for any push metadata routes that dont have a handle
                foreach ($metadata->getRoutesMetaData() as $routeMetaData)
                {
                    if ($routeMetaData->needsHandleCall() && !$routeMetaData->hasHandleCall())
                    {
                        throw DrestException::routeRequiresHandle($routeMetaData->getName());
                    }
                }

        	}
        }

        return ($resourceFound) ? $metadata : null;
    }


    /**
     * Factory method for the Annotation Driver
     *
     * @param AnnotationReader $reader
     * @param array|string $paths
     * @return AnnotationDriver
     */
    public static function create(Annotations\AnnotationReader $reader = null, $paths = array())
    {
        if ($reader == null) {
            $reader = new Annotations\AnnotationReader();
        }

        return new self($reader, $paths);
    }

	public static function registerAnnotations()
	{
		Annotations\AnnotationRegistry::registerFile( __DIR__ . '/DrestAnnotations.php');
	}
}
