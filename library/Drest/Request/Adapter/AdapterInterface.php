<?php
namespace Drest\Request\Adapter;

use Drest\DrestException;
interface AdapterInterface
{

	/**
	 * Request adapter construction
	 * @param object $request
	 */
	public function __construct($request);

	/**
	 * Get all set headers as a key => value array, or a specifc entry when passing $name variable
	 * @return array|string $header
	 */
	public function getHeaders($name = null);

	/**
	 * Get either all post parameters or a specific entry
	 * @return mixed $params an array of all params, or a specific entry
	 */
	public function getPost($name = null);

	/**
	 * Set a post variable - if an array is passed in the $name then post if overwritten with the new values
	 * @param string|array $name
	 * @param string $value
	 */
	public function setPost($name, $value = null);

	/**
	 * Get either all query parameters or a specific entry
	 * @return mixed|array $params an array of all params, or a specific entry
	 */
	public function getQuery($name = null);

	/**
	 * Set a post variable - if an array is passed in the $name then post if overwritten with the new values
	 * @param string|array $name
	 * @param string $value
	 */
	public function setQuery($name, $value = null);

	/**
	 * Get either all cookie parameters or a specific entry
	 * @return mixed $params an array of all cookies, or a specific entry
	 */
	public function getCookie($name = null);

	/**
	 * Get all parameters that have been passed (including anything parsed from the route) - GET|POST|COOKIE|ROUTE
	 * return array $parameters
	 */
	public function getParams();

	/**
	 * Get the low level (adapted) request object
	 * @return mixed $request - The sourced request object, could be symfony / zf etc
	 */
	public function getRequest();

	/**
	 * Set a parameter(s) parsed from the route - if an array is passed in the $name then all route parametes are overwritten with new passed values
	 * @param string|array $name
	 * @param unknown_type $value
	 */
	public function setRouteParam($name, $value = null);

	/**
	 * Get the HTTP verb used on this request
	 * @return string - value should be mapped to a HTTP_METHOD_* class contant
	 * @throws DrestException - if the verb returned is unknown
	 */
	public function getHttpMethod();

	/**
	 * Get either all route parameters or a specific entry
	 * @param mixed $parameters
	 */
	public function getRouteParam($name = null);

	/**
	 * Get the full request Uri
	 * @return string $uri
	 */
	public function getUri();
}