<?php


namespace Drest\Mapping;

/**
 *
 * A class metadata instance that holds all the information for a Drest entity
 * @author Lee
 *
 */
use Drest\DrestException;

class ServiceMetaData
{
	const CONTENT_TYPE_ELEMENT = 1;
	const CONTENT_TYPE_COLLECTION = 2;

    /**
     * A route object defined on this service
     * @var Annotation\Route $route
     */
	protected $route;

	/**
	 * An array of Drest\Writer\InterfaceWriter objects defined on this service
	 * @var array $writers
	 */
	protected $writers = array();

	/**
	 * The service name (must be unique)
	 * @var string $name
	 */
	protected $name;

	/**
	 * Any array of verbs allowed on this service. They match the constant values defined in Drest\Request eg array('GET', 'POST')
	 * @var array $verbs
	 */
	protected $verbs;

	/**
	 * Content type to be used. Can either be a single entity element, or a collection. Contains value of respective constant
	 * @var intger
	 */
	protected $content_type;

	/**
	 * Add the route path. eg '/users/:id'
	 * @param string $route
	 */
	public function addRoute($route_pattern)
	{
        $this->route = $route_pattern;
	}

	/**
	 * Get the name of this service
	 * @return string $name
	 */
	public function getName()
	{
	    return $this->name;
	}

	/**
	 * Sets the content type to be used for this service
	 * @param string $content_type
	 * @throws DrestException if content type is invalid
	 */
	public function setContentType($content_type)
	{
	    $constant = 'self::CONTENT_TYPE_' . strtoupper($content_type);
        if (!defined($constant))
        {
            throw DrestException::unknownContentType($content_type);
        }
        $this->content_type = constant($constant);
	}

	public function getContentType()
	{
	    return $this->content_type;
	}

	/**
	 * Set a writer instance to be used on this resource
	 * @param object|string $writer - can be either an instance of Drest\Writer\InterfaceWriter of a string (shorthand allowed - Json / Xml) referencing the class.
	 */
	public function addWriter($writer)
	{
		if (!is_object($writer) && is_string($writer))
		{
			throw DrestException::writerMustBeObjectOrString();
		}
		if (is_object($writer))
		{
			if (!$writer instanceof \Drest\Writer\InterfaceWriter)
			{
				throw DrestException::unknownWriterClass(get_class($writer));
			}
			$this->writers[get_class($writer)] = $writer;
		} elseif(is_string($writer))
		{
			$classNamespace = 'Drest\\Writer\\';
			if (class_exists($writer, false))
			{
				$this->writers[$writer] = $writer;
			} elseif (class_exists($classNamespace . $writer))
			{
				$this->writers[$classNamespace . $writer] = $classNamespace . $writer;
			} else
			{
				throw DrestException::unknownWriterClass($writer);
			}
		}
	}

	/**
	 * Add verbs that are to be allowed on this service.
	 * @param mixed $verbs = a sinlge or array of verbs valid for this service. eg array('GET', 'PUT')
	 * @throws DrestException if verb is invalid
	 */
	public function addVerbs($verbs)
	{
	    $verbs = (array) $verbs;
	    foreach ($verbs as $verb)
	    {
    	    $verb = strtoupper($verb);
            if (!defined('Drest\Request::METHOD_' . $verb))
            {
                throw DrestException::invalidHttpVerbUsed($verb);
            }
            $this->verbs[] = $verb;
	    }
	}

	/**
	 * Sets a unique reference name for the resource. If other resources are created with this name an exception is thrown (must be unique)
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}


    /**
     * Check if this route matches the request passed
     *
     * Parse this route's pattern, and then compare it to an HTTP resource URI
     * This method was modeled after the techniques demonstrated by Dan Sosedoff at:
     *
     * http://blog.sosedoff.com/2009/09/20/rails-like-php-url-router/
     *
     * @param  string $resourceUri A Request URI
     * @return bool
     */
    public function matches(\Drest\Request\Adapter\AdapterInterface $request)
    {
		if ($this->usesHttpVerbs())
		{
			try {
			    $method = $request->getHttpMethod();
			    if (!in_array($method, $this->verbs))
			    {
			        return false;
			    }
			} catch (DrestException $e)
			{
			    return false;
			}
		}


        //Convert URL params into regex patterns, construct a regex for this route, init params
        $patternAsRegex = preg_replace_callback('#:([\w]+)\+?#', array($this, 'matchesCallback'),
            str_replace(')', ')?', (string) $this->pattern));
        if (substr($this->pattern, -1) === '/') {
            $patternAsRegex .= '?';
        }

        //Cache URL params' names and values if this route matches the current HTTP request
        if (!preg_match('#^' . $patternAsRegex . '$#', $request->getUri(), $paramValues)) {
            return false;
        }
        foreach ($this->paramNames as $name) {
            if (isset($paramValues[$name])) {
                if (isset($this->paramNamesPath[ $name ])) {
                    $this->params[$name] = explode('/', urldecode($paramValues[$name]));
                } else {
                    $this->params[$name] = urldecode($paramValues[$name]);
                }
            }
        }

        return true;
    }

    /**
     * Convert a URL parameter (e.g. ":id", ":id+") into a regular expression
     * @param  array    URL parameters
     * @return string   Regular expression for URL parameter
     */
    protected function matchesCallback($m)
    {
        $this->paramNames[] = $m[1];
        if (isset($this->conditions[ $m[1] ])) {
            return '(?P<' . $m[1] . '>' . $this->conditions[ $m[1] ] . ')';
        }
        if (substr($m[0], -1) === '+') {
            $this->paramNamesPath[ $m[1] ] = 1;

            return '(?P<' . $m[1] . '>.+)';
        }

        return '(?P<' . $m[1] . '>[^/]+)';
    }


    /**
     * Is this route specific to defined HTTP verbs
     */
    public function usesHttpVerbs()
    {
		return empty($this->verbs);
    }

}
