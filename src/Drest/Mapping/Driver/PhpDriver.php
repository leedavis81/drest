<?php

namespace Drest\Mapping\Driver;

use Doctrine\Common\Annotations;
use Drest\Configuration;
use Drest\DrestException;
use Drest\Mapping\Annotation;
use Drest\Mapping;
use Drest\Mapping\RouteMetaData;

/**
 * The AnnotationDriver reads the mapping metadata from doc block annotations.
 * Doesn't require paths / file extensions as entities are pull from the doctrine entity manager
 */
class PhpDriver implements DriverInterface
{
    static $configuration_filepath = null;
    static $configuration_filename = 'configuration.php';
    static $configuration_variable = 'resources';

    /**
     * 
     */
    public static function register(Drest\Configuration $config) {
        $cofiguration_filepath = $config->getAttribute('phpConfigFilePath');

        if($configuration_filepath != null) {
            self::$configuration_filepath = $configuration_filepath;
        } else {
            throw new \RuntimeException('What the shit');
        }
    }

    /**
     * Factory method for the Annotation Driver
     *
     * @param  Annotations\AnnotationReader $reader
     * @param  array|string                 $paths
     * @return AnnotationDriver
     */
    public static function create($paths = [])
    {
        return new self($paths);
    }

    /**
     * The paths to look for mapping files - immutable as classNames as cached, must be passed on construct.
     * @var array
     */
    protected $paths;

    /**
     * Loaded class names
     * @var array
     */
    protected $classNames = [];

    public function __construct($paths = [])
    {
        $this->reader = $reader;
        $this->paths = (array) $paths;

        $this->addExtension('php');
    }

    /**
     * Get all the metadata class names known to this driver.
     * @throws DrestException
     * @return array          $classes
     */
    public function getAllClassNames()
    {
        if (empty($this->classNames)) {
            if (empty($this->paths)) {
                throw DrestException::pathToConfigFilesRequired();
            }
            $classes = [];
            $included = [];
            foreach ($this->paths as $path) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($iterator as $file) {
                    /* @var \SplFileInfo $file */
                    if (!in_array($file->getExtension(), $this->extensions)) {
                        continue;
                    }

                    $path = $file->getRealPath();
                    if (!empty($path)) {
                        require_once $path;
                    }

                    // Register the files we've included here
                    $included[] = $path;
                }
            }

            foreach (get_declared_classes() as $className) {
                $reflClass = new \ReflectionClass($className);
                $sourceFile = $reflClass->getFileName();
                if (in_array($sourceFile, $included) && $this->isDrestResource($className)) {
                    $classes[] = $className;
                }
            }

            $this->classNames = $classes;
        }

        return $this->classNames;
    }

    // /**
    //  * Does the class contain a drest resource object
    //  * @param  string $className
    //  * @return bool
    //  */
    // public function isDrestResource($className)
    // {
    //     $classAnnotations = $this->reader->getClassAnnotations(new \ReflectionClass($className));

    //     foreach ($classAnnotations as $classAnnotation) {
    //         if ($classAnnotation instanceof Annotation\Resource) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    /**
     * Load metadata for a class name
     * @param  object|string         $class - Pass in either the class name, or an instance of that class
     * @return Mapping\ClassMetaData $metaData - return null if metadata couldn't be populated from annotations
     * @throws DrestException
     */
    public function loadMetadataForClass($class)
    {
        $resourceFound = false;

        if (is_string($class)) {
            $class = new \ReflectionClass($class);
        }

        $metadata = new Mapping\ClassMetaData($class);
        foreach ($this->reader->getClassAnnotations($class) as $annotatedObject) {
            if ($annotatedObject instanceof Annotation\Resource) {
                $resourceFound = true;

                if ($annotatedObject->routes === null) {
                    throw DrestException::annotatedResourceRequiresAtLeastOneServiceDefinition($class->name);
                }

                if (is_array($annotatedObject->representations))
                {
                    $metadata->addRepresentations($annotatedObject->representations);
                }

                $this->processRoutes($annotatedObject->routes, $metadata);

                $this->processMethods($class->getMethods(), $metadata);


                // Error for any push metadata routes that don't have a handle
                foreach ($metadata->getRoutesMetaData() as $routeMetaData) {
                    /* @var RouteMetaData $routeMetaData */
                    if ($routeMetaData->needsHandleCall() && !$routeMetaData->hasHandleCall()) {
                        throw DrestException::routeRequiresHandle($routeMetaData->getName());
                    }
                }

            }
        }

        return ($resourceFound) ? $metadata : null;
    }


    /**
     * Process the method
     * @param $methods
     * @param Mapping\ClassMetaData $metadata
     * @throws DrestException
     */
    protected function processMethods($methods, Mapping\ClassMetaData $metadata)
    {
        // Set the handle calls
        foreach ($methods as $method) {
            /* @var \ReflectionMethod $method */
            if ($method->isPublic()) {
                foreach ($this->reader->getMethodAnnotations($method) as $methodAnnotation) {
                    if ($methodAnnotation instanceof Annotation\Handle) {
                        // Make sure the for is not empty
                        if (empty($methodAnnotation->for) || !is_string($methodAnnotation->for)) {
                            throw DrestException::handleForCannotBeEmpty();
                        }
                        if (($routeMetaData = $metadata->getRouteMetaData($methodAnnotation->for)) === false) {
                            throw DrestException::handleAnnotationDoesntMatchRouteName($methodAnnotation->for);
                        }
                        if ($routeMetaData->hasHandleCall()) {
                            // There is already a handle set for this route
                            throw DrestException::handleAlreadyDefinedForRoute($routeMetaData);
                        }
                        $routeMetaData->setHandleCall($method->getName());
                    }
                }
            }
        }
    }

    /**
     * Process all routes defined
     * @param array $routes
     * @param Mapping\ClassMetaData $metadata
     * @throws DrestException
     */
    protected function processRoutes(array $routes, Mapping\ClassMetaData $metadata)
    {
        $originFound = false;
        foreach ($routes as $route) {
            $routeMetaData = new Mapping\RouteMetaData();

            // Set name
            $route->name = preg_replace("/[^a-zA-Z0-9_\s]/", "", $route->name);
            if ($route->name == '') {
                throw DrestException::routeNameIsEmpty();
            }
            if ($metadata->getRouteMetaData($route->name) !== false) {
                throw DrestException::routeAlreadyDefinedWithName($metadata->getClassName(), $route->name);
            }
            $routeMetaData->setName($route->name);

            // Set verbs (will throw if invalid)
            if (isset($route->verbs)) {
                $routeMetaData->setVerbs($route->verbs);
            }

            if (isset($route->collection)) {
                $routeMetaData->setCollection($route->collection);
            }

            // Add the route pattern
            $routeMetaData->setRoutePattern($route->routePattern);

            if (is_array($route->routeConditions)) {
                $routeMetaData->setRouteConditions($route->routeConditions);
            }

            // Set the exposure array
            if (is_array($route->expose)) {
                $routeMetaData->setExpose($route->expose);
            }

            // Set the allow options value
            if (isset($route->allowOptions)) {
                $routeMetaData->setAllowedOptionRequest($route->allowOptions);
            }

            // Add action class
            if (isset($route->action)) {
                $routeMetaData->setActionClass($route->action);
            }

            // If the origin flag is set, set the name on the class meta data
            if (!is_null($route->origin)) {
                if ($originFound) {
                    throw DrestException::resourceCanOnlyHaveOneRouteSetAsOrigin();
                }
                $metadata->originRouteName = $route->name;
                $originFound = true;
            }

            $metadata->addRouteMetaData($routeMetaData);
        }
    }
}
