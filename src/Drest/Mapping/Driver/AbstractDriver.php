<?php

namespace Drest\Mapping\Driver;

use Drest\DrestException;
use Drest\Mapping\ClassMetaData;
use Drest\Mapping\RouteMetaData;


abstract class AbstractDriver implements DriverInterface {

    /**
     * The paths to look for mapping files - immutable as classNames are cached, must be passed on construct.
     * @var array
     */
    protected $paths;

    /**
     * Load metadata for the given class name
     * @param string $className
     * @return ClassMetadata
     */
    abstract public function loadMetadataForClass($className);


    abstract protected function isDrestResource($className);


    public function __construct($paths = []) {
        $this->paths = (array) $paths;
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
     * Process all routes defined
     * @param array $routes
     * @param ClassMetaData $metadata
     * @throws DrestException
     */
    protected function processRoutes(array $routes, ClassMetaData $metadata)
    {
        $originFound = false;
        foreach ($routes as $route) {
            $routeMetaData = new RouteMetaData();

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

            // Set disable expose lookup
            if (isset($route['disableExpose'])) {
                $routeMetaData->setDisableExpose((bool) $route['disableExpose']);
            }            

            // Set the allow options value
            if (isset($route['allowOptions'])) {
                $routeMetaData->setAllowedOptionRequest($route['allowOptions']);
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