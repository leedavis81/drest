<?php

namespace Drest\Mapping\Driver;

use Doctrine\Common\Annotations;
use Drest\DrestException;
use Drest\Mapping\Annotation;
use Drest\Mapping;

/**
 * The PhpDriver reads a configuration file (config.php) rather than utilizing annotations.
 */
class PhpDriver extends AbstractDriver
{
    /**
     * The classes (resources) from config.php 
     * @var array
     */
    protected $classes = [];

    /**
     * Whether the classes have been read in
     * @var bool
     */
    protected $classesLoaded = false;


    public function __construct($paths)
    {
        parent::__construct($paths);
    }

    /**
     * Factory method for the Annotation Driver
     *
     * @param  array|string $paths
     * @return self
     */
    public static function create($paths = [])
    {
        return new static($paths);
    }

    /**
     * Get all the metadata class names known to this driver.
     * @return array
     * @throws DrestException
     * @throws DriverException
     */
    public function getAllClassNames()
    {
        if (empty($this->classes)) {
            if (empty($this->paths)) {
                throw DrestException::pathToConfigFilesRequired();
            }

            foreach ($this->paths as $path)
            {
                if(!file_exists($path)) {
                    throw DriverException::configurationFileDoesntExist($path);
                }

                $resources = include $path;

                if(!is_array($resources)) {
                    throw DriverException::configurationFileIsInvalid('Php');
                }

                $this->classes = array_merge($this->classes, $resources);
            }
        }

        return array_keys($this->classes);
    }

    /**
     * Load metadata for a class name
     * @param  object|string         $className - Pass in either the class name, or an instance of that class
     * @return Mapping\ClassMetaData $metaData - return null if metadata couldn't be populated from annotations
     * @throws DrestException
     */
    public function loadMetadataForClass($className)
    {
        if (!$this->classesLoaded)
        {
            $this->getAllClassNames();
            $this->classesLoaded = true;
        }

        $class = new \ReflectionClass($className);

        $metadata = new Mapping\ClassMetaData($class);

        if(!isset($this->classes[$className])) {
            return null;
        }

        $resource = $this->classes[$className];

        if ($resource['routes'] === null) {
            throw DrestException::annotatedResourceRequiresAtLeastOneServiceDefinition($resource['name']);
        }

        if (is_array($resource['representations']))
        {
            $metadata->addRepresentations($resource['representations']);
        }

        $this->processRoutes($resource['routes'], $metadata);

        $this->processMethods($resource, $metadata);

        $this->checkHandleCalls($metadata->getRoutesMetaData());
        
        return $metadata;
    }

    /**
     * Does the class contain a drest resource object
     *
     * @param  string $className
     * @return bool
     */
    public function isDrestResource($className)
    {
        if(!in_array($className, array_keys($this->classes)))
        {
            return false;
        }
        return true;
    }


    /**
     * Process the method
     * @param $resource
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
}
