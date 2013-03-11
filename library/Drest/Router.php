<?php
namespace Drest;



/**
 *
 * Drest Router
 * @author Lee
 */
class Router
{

	/**
	 *A collection of registered route objects
	 * @var array $routes
	 */
	protected $routes = array();

	/**
	 * A collection of routes that has been populated on ::getMatchedRoutes()
	 * @var array $matchedRoutes
	 */
	protected $matchedRoutes = array();


	/**
	 * (non-PHPdoc)
	 * @see Symfony\Component\Routing.Router::match()
	 */
	public function getMatchedRoutes(\Drest\Request $request)
	{

	}

	/**
	 * Add a route object pulled from annotations into the router stack
	 * @param \Drest\Mapping\Annotation\Route $route
	 */
	public function addRoute(\Drest\Mapping\Annotation\Route $route)
	{
		$this->routes[$route->name] = $route;
	}

	/**
	 *
	 * A check to see if the router object has already been populated with a route object by $name
	 * @param sting $name
	 */
	public function hasRoute($name)
	{
		return isset($this->routes[$name]);
	}



}