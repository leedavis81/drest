<?php
namespace Drest;

use Drest\DrestException,
	Drest\Response\Adapter;


class Response
{
    // Info Codes
    const STATUS_CODE_100 = 100;
    const STATUS_CODE_101 = 101;
    const STATUS_CODE_102 = 102;
    // Success
    const STATUS_CODE_200 = 200;
    const STATUS_CODE_201 = 201;
    const STATUS_CODE_202 = 202;
    const STATUS_CODE_203 = 203;
    const STATUS_CODE_204 = 204;
    const STATUS_CODE_205 = 205;
    const STATUS_CODE_206 = 206;
    const STATUS_CODE_207 = 207;
    const STATUS_CODE_208 = 208;
    // Redirection
    const STATUS_CODE_300 = 300;
    const STATUS_CODE_301 = 301;
    const STATUS_CODE_302 = 302;
    const STATUS_CODE_303 = 303;
    const STATUS_CODE_304 = 304;
    const STATUS_CODE_305 = 305;
    const STATUS_CODE_306 = 306;
    const STATUS_CODE_307 = 307;
    // Client Error
    const STATUS_CODE_400 = 400;
    const STATUS_CODE_401 = 401;
    const STATUS_CODE_402 = 402;
    const STATUS_CODE_403 = 403;
    const STATUS_CODE_404 = 404;
    const STATUS_CODE_405 = 405;
    const STATUS_CODE_406 = 406;
    const STATUS_CODE_407 = 407;
    const STATUS_CODE_408 = 408;
    const STATUS_CODE_409 = 409;
    const STATUS_CODE_410 = 410;
    const STATUS_CODE_411 = 411;
    const STATUS_CODE_412 = 412;
    const STATUS_CODE_413 = 413;
    const STATUS_CODE_414 = 414;
    const STATUS_CODE_415 = 415;
    const STATUS_CODE_416 = 416;
    const STATUS_CODE_417 = 417;
    const STATUS_CODE_418 = 418;
    const STATUS_CODE_422 = 422;
    const STATUS_CODE_423 = 423;
    const STATUS_CODE_424 = 424;
    const STATUS_CODE_425 = 425;
    const STATUS_CODE_426 = 426;
    const STATUS_CODE_428 = 428;
    const STATUS_CODE_429 = 429;
    const STATUS_CODE_431 = 431;
    // Server Error
    const STATUS_CODE_500 = 500;
    const STATUS_CODE_501 = 501;
    const STATUS_CODE_502 = 502;
    const STATUS_CODE_503 = 503;
    const STATUS_CODE_504 = 504;
    const STATUS_CODE_505 = 505;
    const STATUS_CODE_506 = 506;
    const STATUS_CODE_507 = 507;
    const STATUS_CODE_508 = 508;
    const STATUS_CODE_511 = 511;
    /**#@-*/

    /**
     * Status Phrases
     * @var array $statusPhrases
     */
    protected static $statusPhrases = array(
        self::STATUS_CODE_100 => 'Continue',
        self::STATUS_CODE_101 => 'Switching Protocols',
        self::STATUS_CODE_102 => 'Processing',
        self::STATUS_CODE_200 => 'OK',
        self::STATUS_CODE_201 => 'Created',
        self::STATUS_CODE_202 => 'Accepted',
        self::STATUS_CODE_203 => 'Non-Authoritative Information',
        self::STATUS_CODE_204 => 'No Content',
        self::STATUS_CODE_205 => 'Reset Content',
        self::STATUS_CODE_206 => 'Partial Content',
        self::STATUS_CODE_207 => 'Multi-status',
        self::STATUS_CODE_208 => 'Already Reported',
        self::STATUS_CODE_300 => 'Multiple Choices',
        self::STATUS_CODE_301 => 'Moved Permanently',
        self::STATUS_CODE_302 => 'Found',
        self::STATUS_CODE_303 => 'See Other',
        self::STATUS_CODE_304 => 'Not Modified',
        self::STATUS_CODE_305 => 'Use Proxy',
        self::STATUS_CODE_306 => 'Switch Proxy',
        self::STATUS_CODE_307 => 'Temporary Redirect',
        self::STATUS_CODE_400 => 'Bad Request',
        self::STATUS_CODE_401 => 'Unauthorized',
        self::STATUS_CODE_402 => 'Payment Required',
        self::STATUS_CODE_403 => 'Forbidden',
        self::STATUS_CODE_404 => 'Not Found',
        self::STATUS_CODE_405 => 'Method Not Allowed',
        self::STATUS_CODE_406 => 'Not Acceptable',
        self::STATUS_CODE_407 => 'Proxy Authentication Required',
        self::STATUS_CODE_408 => 'Request Time-out',
        self::STATUS_CODE_409 => 'Conflict',
        self::STATUS_CODE_410 => 'Gone',
        self::STATUS_CODE_411 => 'Length Required',
        self::STATUS_CODE_412 => 'Precondition Failed',
        self::STATUS_CODE_413 => 'Request Entity Too Large',
        self::STATUS_CODE_414 => 'Request-URI Too Large',
        self::STATUS_CODE_415 => 'Unsupported Media Type',
        self::STATUS_CODE_416 => 'Requested range not satisfiable',
        self::STATUS_CODE_417 => 'Expectation Failed',
        self::STATUS_CODE_418 => 'I\'m a teapot',
        self::STATUS_CODE_422 => 'Unprocessable Entity',
        self::STATUS_CODE_423 => 'Locked',
        self::STATUS_CODE_424 => 'Failed Dependency',
        self::STATUS_CODE_425 => 'Unordered Collection',
        self::STATUS_CODE_426 => 'Upgrade Required',
        self::STATUS_CODE_428 => 'Precondition Required',
        self::STATUS_CODE_429 => 'Too Many Requests',
        self::STATUS_CODE_431 => 'Request Header Fields Too Large',
        self::STATUS_CODE_500 => 'Internal Server Error',
        self::STATUS_CODE_501 => 'Not Implemented',
        self::STATUS_CODE_502 => 'Bad Gateway',
        self::STATUS_CODE_503 => 'Service Unavailable',
        self::STATUS_CODE_504 => 'Gateway Time-out',
        self::STATUS_CODE_505 => 'HTTP Version not supported',
        self::STATUS_CODE_506 => 'Variant Also Negotiates',
        self::STATUS_CODE_507 => 'Insufficient Storage',
        self::STATUS_CODE_508 => 'Loop Detected',
        self::STATUS_CODE_511 => 'Network Authentication Required',
    );

	/**
	 * Adapter class used for response handling
	 * @var Drest\Response\Adapter\AdapterAbstract $adapter
	 */
	protected $adapter;

	/**
	 * Construct an instance of Drest Response object
	 * @param mixed $response prefered adapted response type
	 * @param array $adapterClasses - an array of adapter classes available
	 */
	public function __construct($response_object = null, array $adapterClasses = array())
	{
		$defaultClass = 'Symfony\Component\HttpFoundation\Response';
		if (is_null($response_object))
		{
			if (!class_exists($defaultClass))
			{
				throw DrestException::noResponseObjectDefinedAndCantInstantiateDefaultType($defaultClass);
			}
			// Default to using symfony's request object
			$this->adapter = new Adapter\Symfony2($defaultClass::create());
		} else if (is_object($response_object))
		{
			foreach ($adapterClasses as $adapterClass)
            {
		        $adaptedClassName = $adapterClass::getAdaptedClassName();
		        if ($response_object instanceof $adaptedClassName)
		        {
		            $adaptedObj = new $adapterClass($response_object);
		            if ($adaptedObj instanceof \Drest\Response\Adapter\AdapterAbstract)
		            {
    		            $this->adapter = $adaptedObj;
    		            return;
		            }
		        }
		    }
            throw DrestException::unknownAdapterForResponseObject($response_object);
		} else
		{
			throw DrestException::invalidResponseObjectPassed();
		}
	}

	/**
	 * Get either all HTTP header values or a specific entry
	 * @param unknown_type $name
	 * @return array $headers an array of all headers, or an array of a specific entry
	 */
	public function getHttpHeader($name = null)
	{
	   return $this->adapter->getHttpHeader($name);
	}

	/**
	 * Set an HTTP header value - if an array is passed in the $name then all headers are overwritten with the new values
	 * @param string|array $name
	 * @param string $value
	 */
	public function setHttpHeader($name, $value = null)
	{
        $this->adapter->setHttpHeader($name, $value);
	}

	/**
	 * Get the body of the response document
	 * @return string body
	 */
	public function getBody()
	{
	    return $this->adapter->getBody();
	}

	/**
	 * Set the body of the response document. This can be either a string or an object with __toString implemented
	 * @param string|object $body
	 */
	public function setBody($body)
	{
	    $this->adapter->setBody($body);
	}

	/**
	 * Get the HTTP status code
	 * @return integer $code
	 */
	public function getStatusCode()
	{
        return $this->adapter->getStatusCode();
	}

	/**
	 * Set the status code
	 * @param integer $code - must reflect one of the STATUS_CODE_* constants on this class
	 */
	public function setStatusCode($code)
	{
	    if (!array_key_exists($code, self::$statusPhrases))
	    {
	        throw DrestException::invalidHttpStatusCode($code);
	    }
        $this->adapter->setStatusCode($code, self::$statusPhrases[$code]);
	}

	/**
	 * Get the adapted response object (would be fw specific type)
	 * @return object $response
	 */
	public function getResponse()
	{
	    return $this->adapter->getResponse();
	}

	/**
	 * Echo the adapted response object
	 */
	public function __toString()
	{
	    return $this->adapter->toString();
	}

	/**
	 * Factory call to create a Drest response object
	 * @param mixed $response_object prefered response object
	 * @param array $adapterClasses - an array of adapter classes available
	 */
	public static function create($response_object = null, array $adapterClasses = array())
	{
		return new self($response_object, $adapterClasses);
	}

}