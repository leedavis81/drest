<?php

namespace Drest\Mapping\Driver;

use Doctrine\Common\Annotations;
use Drest\Configuration;
use Drest\DrestException;
use Drest\Mapping\Annotation;
use Drest\Mapping;
use Drest\Mapping\RouteMetaData;

/**
 * The PhpDriver reads a configuration file (config.php) rather than utilizing annotations.
 */
class PhpDriver extends AbstractDriver
{

    static $configuration_filepath = null;
    static $configuration_filename = null;

    /**
     * The classes (resources) from config.php 
     * @var array
     */
    protected $classes = [];

    public function __construct($paths)
    {
        parent::__construct($paths);

        $filename = self::$configuration_filepath . DIRECTORY_SEPARATOR . self::$configuration_filename;

        $file_parts = pathinfo($filename);

        if($file_parts['extension'] == 'php') {

            if(!file_exists($filename)) { 
                throw new \RuntimeException('The configuration file does not exist at this path: ' . $filename);
            }

            $resources = include($filename);

            if(!is_array($resources)) {
                throw new \RuntimeException('The configuration file does not return the configuration: ' . $filename);
            }

            $this->classes = $resources;
        }  
    }

    /**
     * 
     */
    public static function register(Configuration $config) {
        self::$configuration_filepath = $config->getAttribute('configFilePath');
        self::$configuration_filename = $config->getAttribute('configFileName');
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
        if(static::$configuration_filepath == null || static::$configuration_filename == null) {
            throw new \RuntimeException('Configuration file path or file name is not set.');
        }
        return new static($paths, static::$configuration_filepath . DIRECTORY_SEPARATOR . static::$configuration_filename);
    }

    /**
     * Load metadata for a class name
     * @param  object|string         $class - Pass in either the class name, or an instance of that class
     * @return Mapping\ClassMetaData $metaData - return null if metadata couldn't be populated from annotations
     * @throws DrestException
     */
    public function loadMetadataForClass($class)
    {
        if (is_string($class)) {
            $metadata = new Mapping\ClassMetaData(new \ReflectionClass($class));
        } else {
            $metadata = new Mapping\ClassMetaData($class);
        }

        if(!isset($this->classes[$class])) {
            throw new \RuntimeException('The class is not set: ' . $class);   
        }

        $resource = $this->classes[$class];

        if ($resource['routes'] === null) {
            throw DrestException::annotatedResourceRequiresAtLeastOneServiceDefinition($resource['name']);
        }

        if (is_array($resource['representations']))
        {
            $metadata->addRepresentations($resource['representations']);
        }

        $this->processRoutes($resource['routes'], $metadata);

        $this->processMethods($resource, $metadata);

        // Error for any push metadata routes that don't have a handle
        foreach ($metadata->getRoutesMetaData() as $routeMetaData) {
            /* @var RouteMetaData $routeMetaData */
            if ($routeMetaData->needsHandleCall() && !$routeMetaData->hasHandleCall()) {
                throw DrestException::routeRequiresHandle($routeMetaData->getName());
            }
        }
        return $metadata;
    }

    public function isDrestResource($className) {
        if(in_array($className, array_keys($this->classes))) {
            return true;
        }
    }


    /**
     * Process the method
     * @param $methods
     * @param Mapping\ClassMetaData $metadata
     * @throws DrestException
     */
    protected function processMethods($resource, Mapping\ClassMetaData $metadata)
    {
        /* @var \ReflectionMethod $method */
        foreach ($resource['routes'] as $route) {
            // Make sure the for is not empty
            if (!isset($route['name']) || !is_string($route['name'])) {
                throw DrestException::handleForCannotBeEmpty();
            }
            if (($routeMetaData = $metadata->getRouteMetaData($route['name'])) === false) {
                throw DrestException::handleAnnotationDoesntMatchRouteName($route['name']);
            }
            if ($routeMetaData->hasHandleCall()) {
                // There is already a handle set for this route
                throw DrestException::handleAlreadyDefinedForRoute($routeMetaData);
            }

            // Set the handle
            if (isset($route['handle_call'])) {
                $routeMetaData->setHandleCall($route['handle_call']);
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
            $route['name'] = preg_replace("/[^a-zA-Z0-9_\s]/", "", $route['name']);
            if ($route['name'] == '') {
                throw DrestException::routeNameIsEmpty();
            }
            if ($metadata->getRouteMetaData($route['name']) !== false) {
                throw DrestException::routeAlreadyDefinedWithName($metadata->getClassName(), $route['name']);
            }
            $routeMetaData->setName($route['name']);

            // Set verbs (will throw if invalid)
            if (isset($route['verbs'])) {
                $routeMetaData->setVerbs($route['verbs']);
            }

            if (isset($route['collection'])) {
                $routeMetaData->setCollection($route['collection']);
            }

            // Add the route pattern
            $routeMetaData->setRoutePattern($route['routePattern']);

            if (isset($route['routeConditions']) && is_array($route['routeConditions'])) {
                $routeMetaData->setRouteConditions($route['routeConditions']);
            }

            // Set the exposure array
            if (isset($route['expose']) && is_array($route['expose'])) {
                $routeMetaData->setExpose($route['expose']);
            }

            // Set the allow options value
            if (isset($route['allowOptions'])) {
                $routeMetaData->setAllowedOptionRequest($route['allowOptions']);
            }

            // Add action class
            if (isset($route['action'])) {
                $routeMetaData->setActionClass($route['action']);
            }

            // If the origin flag is set, set the name on the class meta data
            if (isset($route['origin']) && !is_null($route['origin'])) {
                if ($originFound) {
                    throw DrestException::resourceCanOnlyHaveOneRouteSetAsOrigin();
                }
                $metadata->originRouteName = $route['name'];
                $originFound = true;
            }

            $metadata->addRouteMetaData($routeMetaData);
        }
    }
}
