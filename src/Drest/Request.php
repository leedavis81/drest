<?php
namespace Drest;

use Drest\DrestException,
	Drest\Request\Adapter;


class Request
{

    const METHOD_OPTIONS  = 'OPTIONS';
    const METHOD_GET      = 'GET';
    const METHOD_HEAD     = 'HEAD';
    const METHOD_POST     = 'POST';
    const METHOD_PUT      = 'PUT';
    const METHOD_DELETE   = 'DELETE';
    const METHOD_TRACE    = 'TRACE';
    const METHOD_CONNECT  = 'CONNECT';
    const METHOD_PATCH    = 'PATCH';
    const METHOD_PROPFIND = 'PROPFIND';

	/**
	 * Adapter class used for request handling
	 * @var Drest\Request\Adapter\AdapterAbstract $adapter
	 */
	protected $adapter;

	/**
	 * Construct an instance of Drest Request object
	 * @param mixed $request prefered router type
	 */
	public function __construct($request_object = null)
	{
	    //@todo: remove this, the request object should have no knowlegde of the adapted classes
		$zf2class = 'Zend\Http\Request';
		$sy2class = 'Symfony\Component\HttpFoundation\Request';
		if (is_null($request_object))
		{
			if (!class_exists($sy2class))
			{
				throw DrestException::noRequestObjectDefinedAndCantInstantiateDefaultType($sy2class);
			}
			// Default to using symfony's request object
			$this->adapter = new Adapter\Symfony2(\Symfony\Component\HttpFoundation\Request::createFromGlobals());
		} else if (is_object($request_object))
		{
			if ($request_object instanceof $zf2class)
			{
				$this->adapter = new Adapter\ZendFramework2($request_object);
			} elseif ($request_object instanceof $sy2class)
			{
				$this->adapter = new Adapter\Symfony2($request_object);
			} else
			{
				throw DrestException::unknownAdapterForRequestObject($request_object);
			}
		} else
		{
			throw DrestException::invalidRequestObjectPassed();
		}
	}


	/**
	 * Get all set headers as a key => value array, or a specifc entry when passing $name variable
	 * @return array|string $header
	 */
	public function getHeaders($name = null)
	{
	    return $this->adapter->getHeaders($name);
	}

	/**
	 * Get either all post parameters or a specific entry
	 * @return mixed $params an array of all params, or a specific entry
	 */
	public function getPost($name = null)
	{
	    return $this->adapter->getPost($name);
	}

	/**
	 * Set a post variable - if an array is passed in the $name then post if overwritten with the new values
	 * @param string|array $name
	 * @param string $value
	 */
	public function setPost($name, $value = null)
	{
	    $this->adapter->setPost($name, $value);
	}

	/**
	 * Get either all query parameters or a specific entry
	 * @return mixed|array $params an array of all params, or a specific entry
	 */
	public function getQuery($name = null)
	{
	    return $this->adapter->getQuery($name);
	}

	/**
	 * Set a post variable - if an array is passed in the $name then post if overwritten with the new values
	 * @param string|array $name
	 * @param string $value
	 */
	public function setQuery($name, $value = null)
	{
	    $this->adapter->setQuery($name, $value);
	}

	/**
	 * Get either all cookie parameters or a specific entry
	 * @return mixed $params an array of all cookies, or a specific entry
	 */
	public function getCookie($name = null)
	{
	    return $this->adapter->getCookie($name);
	}

	/**
	 * Get all parameters that have been passed (including anything parsed from the route) - GET|POST|COOKIE|ROUTE
	 * @return mixed $params an array of all params, or a specific entry
	 * return array $parameters
	 */
	public function getParams($name = null)
	{
	    return $this->adapter->getParams();
	}

	/**
	 * Get the low level (adapted) request object
	 * @return mixed $request - The sourced request object, could be symfony / zf etc
	 */
	public function getRequest()
	{
	    return $this->adapter->getRequest();
	}

	/**
	 * Set a parameter(s) parsed from the route - if an array is passed in the $name then all route parametes are overwritten with new passed values
	 * @param string|array $name
	 * @param unknown_type $value
	 */
	public function setRouteParam($name, $value = null)
	{
	    $this->adapter->setRouteParam($name, $value);
	}

	/**
	 * Get either all route parameters or a specific entry
	 * @param mixed $parameters
	 */
	public function getRouteParam($name = null)
	{
	    return $this->adapter->getRouteParam($name);
	}

	/**
	 * Get the HTTP verb used on this request
	 * @return string - value should be mapped to a HTTP_METHOD_* class contant
	 * @throws DrestException - if the verb returned is unknown
	 */
	public function getHttpMethod()
	{
	    return $this->adapter->getHttpMethod();
	}

	/**
	 * Get the request document body
	 * @return string
	 */
	public function getBody()
	{
	    return $this->adapter->getBody();
	}

	/**
	 * Get the full request Uri
	 * @return string $uri
	 */
	public function getUri()
	{
	    return $this->adapter->getUri();
	}

	/**
	 * Get the resource location (no path / name included)
	 * @return string $url
	 */
	public function getUrl()
	{
        return $this->adapter->getUrl();
	}

	/**
	 * Get the URI path - excludes URL meta data such as query parameters, hashes, extentions (?#!.)
	 * @return string $path
	 */
	public function getPath()
	{
	    return $this->adapter->getPath();
	}

    /**
     * Get the URI extension (if present)
     * @return string $extension
     */
	public function getExtension()
	{
        return $this->adapter->getExtension();
	}

	/**
	 * Factory call to create a Drest request object
	 * @param mixed $request_object prefered response object
	 */
	public static function create($request_object = null)
	{
		return new self($request_object);
	}

}