<?php
namespace Drest;


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
	 * @param RequestInterface $request
	 * @param boolean $matchVerb - Whether you want to match the route using the request HTTP verb
	 * @return array $routes sends back an array of routes that have matched
	 */
	public function getMatchedRoutes(Request $request, $matchVerb = true)
	{
		$matches = array();

		foreach ($this->routes as $route)
		{
		    if (sizeof($this->routeBasePaths) > 0)
		    {
		        foreach ($this->routeBasePaths as $basePath)
		        {
		            if ($route->matches($request, $matchVerb, $basePath))
        		    {
        		        $matches[] = $route;
        		    }
		        }
		    } else
		    {
    		    if ($route->matches($request, $matchVerb))
    		    {
    		        $matches[] = $route;
    		    }
		    }
		}
		return $matches;
	}

	/**
	 * Register a route definition (pulled from annotations) into the router stack
	 * @param \Drest\Mapping\RouteMetaData $route
	 */
	public function registerRoute($route)
	{
		$this->routes[$route->getName()] = $route;
	}

	/**
	 * A check to see if the router object has already been populated with a route object by $name
	 * @param sting $name
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