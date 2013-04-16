<?php
namespace Drest\Response\Adapter;

interface AdapterInterface
{

	/**
	 * Response adapter construction
	 * @param object $response
	 */
	public function __construct($response);

	/**
	 * Reponse objects must be echoable, this call will typically be passed to the adapted response object
	 */
	public function __toString();

	/**
	 * Get the body of the response document
	 * @return string body
	 */
	public function getBody();

	/**
	 * Set the body of the response document. This can be either a string or an object with __toString implemented
	 * @param string|object $body
	 */
	public function setBody($body);

	/**
	 * Set an HTTP header value - if an array is passed in the $name then all headers are overwritten with the new values
	 * @param string|array $name
	 * @param string $value
	 */
	public function setHttpHeader($name, $value = null);

	/**
	 * Get either all HTTP header values or a specific entry
	 * @param unknown_type $name
	 * @return mixed $headers an array of all headers, or a specific entry
	 */
	public function getHttpHeader($name = null);

	/**
	 * Set the status code
	 * @param integer $code
	 * @param string $text - HTTP status text to be used
	 */
	public function setStatusCode($code, $text);

	/**
	 * Get the HTTP status code
	 * @return integer $code
	 */
	public function getStatusCode();
}