<?php
namespace Drest;

use Drest\Router\RouterInterface,
	Drest\Request\Adapter\AdapterInterface as RequestInterface;

/**
 * Drest Router
 * @author Lee
 */
class Router implements RouterInterface
{

	/**
	 *A collection of registered route objects
	 * @var array $routes
	 */
	protected $routes = array();


	/**
	 *
	 * Enter description here ...
	 * @param RequestInterface $request
	 * @param integer $limit max number number of routes to match before returning
	 * @return array $routes sends back an array of routes that have matched
	 */
	public function getMatchedRoutes(RequestInterface $request, $limit = -1)
	{
		$matches = array();
		$url = '/users/1';

		foreach ($this->routes as $route)
		{

			$route = new \Drest\Mapping\Annotation\Route();

			$route->matches($request)
		}
		return $matches;
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