<?php
namespace Drest\Request\Adapter;

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
	 * Get all parameters that have been passed - GET|POST|COOKIE
	 * return array $parameters
	 */
	public function getParams();

	/**
	 * Get the low level (adapted) request object
	 * @return mixed $request - The sourced request object, could be symfony / zf etc
	 */
	public function getRequest();
}