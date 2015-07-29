<?php
/**
 * This file is part of the Drest package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Lee Davis
 * @copyright Copyright (c) Lee Davis <@leedavis81>
 * @link https://github.com/leedavis81/drest/blob/master/LICENSE
 * @license http://opensource.org/licenses/MIT The MIT X License (MIT)
 */
namespace Drest;

use Drest\Mapping\RouteMetaData;
use DrestCommon\Request\Request;

/**
 * Drest Router
 * @author Lee
 */
class Router
{
    /**
     * A collection of registered route objects
     * @var array $routes
     */
    protected $routes = array();

    /**
     * An array of Route Base Paths
     * @var array $routeBasePaths
     */
    protected $routeBasePaths = array();

    /**
     * Get matched routes
     * @param  Request $request
     * @param  boolean $matchVerb - Whether you want to match the route using the request HTTP verb
     * @return array   $routes sends back an array of routes that have matched
     */
    public function getMatchedRoutes(Request $request, $matchVerb = true)
    {
        $matches = array();

        foreach ($this->routes as $route) {
            /* @var RouteMetaData $route */
            if (sizeof($this->routeBasePaths) > 0) {
                foreach ($this->routeBasePaths as $basePath) {
                    if ($route->matches($request, $matchVerb, $basePath)) {
                        $matches[] = $route;
                    }
                }
                return $matches;
            }

            if ($route->matches($request, $matchVerb)) {
                $matches[] = $route;
            }
        }

        return $matches;
    }

    /**
     * Register a route definition (pulled from annotations) into the router stack
     * @param RouteMetaData $route
     */
    public function registerRoute(RouteMetaData $route)
    {
        $this->routes[$route->getName()] = $route;
    }

    /**
     * Register an array of routes
     * @param RouteMetaData[] $routes
     */
    public function registerRoutes(array $routes)
    {
        foreach ($routes as $route)
        {
            if (!$route instanceof RouteMetaData)
            {
                continue;
            }
            $this->registerRoute($route);
        }
    }

    /**
     * A check to see if the router object has already been populated with a route object by $name
     * @param  string $name
     * @return bool
     */
    public function hasRoute($name)
    {
        return isset($this->routes[$name]);
    }

    /**
     * Set any route base paths
     * @param array $basePaths
     */
    public function setRouteBasePaths(array $basePaths)
    {
        $this->routeBasePaths = $basePaths;
    }
}
