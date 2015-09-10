<?php

namespace Drest\Mapping\Driver;

use Drest\DrestException;


abstract class AbstractDriver implements DriverInterface {

    /**
     * The paths to look for mapping files - immutable as classNames as cached, must be passed on construct.
     * @var array
     */
    protected $paths;

    /**
     * Extensions of the files to read
     * @var array $paths
     */
    protected $extensions = [];

    /**
     * The array of class names.
     * @var array
     */
    protected $classNames = [];

    /**
     * Load metadata for the given class name
     * @param string $className
     * @return \Drest\Mapping\ClassMetadata
     */
    abstract public function loadMetadataForClass($className);


    abstract protected function isDrestResource($className);


    public function __construct($paths = []) {
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

    /**
     * Check handle calls.
     * @param array $routeMetaData
     */
    public function checkHandleCalls($routeMetaDataArray) {
        // Error for any push metadata routes that don't have a handle
        foreach ($routeMetaDataArray as $routeMetaData) {
            /* @var RouteMetaData $routeMetaData */
            if ($routeMetaData->needsHandleCall() && !$routeMetaData->hasHandleCall()) {
                throw DrestException::routeRequiresHandle($routeMetaData->getName());
            }
        }
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
     * @param string $extension - can be a string or an array of extensions
     */
    public function addExtension($extension)
    {
        $extension = (array) $extension;
        foreach ($extension as $ext) {
            if (!in_array($ext, $this->extensions)) {
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
        if (is_null($extension)) {
            $this->extensions = [];
        } else {
            $offset = array_search($extension, $this->extensions);
            if ($offset !== false) {
                unset($this->extensions[$offset]);
            }
        }
    }

    /**
     * Process all routes defined
     * @param array $routes
     * @param Mapping\ClassMetaData $metadata
     * @throws DrestException
     */
    protected function processRoutes(array $routes, \Drest\Mapping\ClassMetaData $metadata)
    {
        $originFound = false;
        foreach ($routes as $route) {
            $routeMetaData = new \Drest\Mapping\RouteMetaData();

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