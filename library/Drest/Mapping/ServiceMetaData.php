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
	 * This service objects parent
	 * @var Drest\Mapping\ClassMetaData $classMetaData
	 */
	protected $classMetaData;

    /**
     * A string route pattern to be matched on. eg /user/:id
     * @var string $route_pattern
     */
	protected $route_pattern;

	/**
	 * Any custom regex conditions that are needed when matching a route param component. Eg array('year' => '(19|20)\d\d')
	 * @var array $route_conditions
	 */
	protected $route_conditions = array();

    /**
     * Key-value array of URL parameter names
     * @var array $param_names
     */
	protected $param_names = array();

	/**
	 * Key-value array of URL parameters with + at the end
	 * @var array $param_names_path
	 */
	protected $param_names_path = array();

	/**
	 * Key-value array of URL parameters populated after a match has been successful
	 * - These can only be set upon a match
	 * @var array $params
	 */
	protected $params;

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
	 * @var integer
	 */
	protected $content_type;

	/**
	 * The repository function to be called upon successful routing
	 * @var string
	 */
	protected $repository_method;


	/**
	 * Set this objects parent metadata class
	 * @param ClassMetaData $classMetaData
	 */
	public function setClassMetaData(ClassMetaData $classMetaData)
	{
        $this->classMetaData = $classMetaData;
	}

	/**
	 * Get this classes metadata object
	 * @return Drest\Mapping\ClassMetaData $classMetaData
	 */
	public function getClassMetaData()
	{
	    return $this->classMetaData;
	}

	/**
	 * Get this services route pattern
	 * @return string $route_pattern
	 */
	public function getRoutePattern()
	{
	    return $this->routePattern;
	}

	/**
	 * Add the route path. eg '/users/:id'
	 * @param string $route
	 */
	public function setRoutePattern($route_pattern)
	{
        $this->route_pattern = $route_pattern;
	}

	/**
	 * Add an array of route conditions
	 */
	public function setRouteConditions(array $route_conditions)
	{
        foreach ($route_conditions as $field => $condition)
        {
            $this->route_conditions[preg_replace("/[^a-zA-Z0-9_]+/", "", $field)] = $condition;
        }
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
	 * Sets a unique reference name for the resource. If other resources are created with this name an exception is thrown (must be unique)
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Get the content type to be used for this service
	 * @return string $content_type
	 */
	public function getContentType()
	{
	    return $this->content_type;
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

	/**
	 * Get an array of verbs that are allowed on this service
	 * @return @array $verbs
	 */
	public function getVerbs()
	{
	    return $this->verbs;
	}

	/**
	 * Add verbs that are to be allowed on this service.
	 * @param mixed $verbs = a sinlge or array of verbs valid for this service. eg array('GET', 'PUT')
	 * @throws DrestException if verb is invalid
	 */
	public function setVerbs($verbs)
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
	 * Get the repository method to be used
	 * @return string
	 */
	public function getRepositoryMethod()
	{
	    return $this->repository_method;
	}

	/**
	 * Get any params that were set after a sucessful match
	 * @return array $params
	 */
	public function getParams()
	{
        return $this->params;
	}

	/**
	 * Set the repository method to be used upon routing match
	 * @param string $repository_method
	 */
	public function setRepositoryMethod($repository_method)
	{
	    $this->repository_method = $repository_method;
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
        $patternAsRegex = preg_replace_callback('#:([\w]+)\+?#', array($this, 'matchesCallback'), str_replace(')', ')?', (string) $this->route_pattern));
        if (substr($this->route_pattern, -1) === '/')
        {
            $patternAsRegex .= '?';
        }

        //Cache URL params' names and values if this route matches the current HTTP request
        if (!preg_match('#^' . $patternAsRegex . '$#', $request->getUri(), $paramValues))
        {
            return false;
        }

        foreach ($this->param_names as $name)
        {
            if (isset($paramValues[$name]))
            {
                if (isset($this->param_names_path[$name]))
                {
                    $this->params[$name] = explode('/', urldecode($paramValues[$name]));
                } else
                {
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
        $this->param_names[] = $m[1];
        if (isset($this->route_conditions[$m[1]]))
        {
            return '(?P<' . $m[1] . '>' . $this->route_conditions[$m[1]] . ')';
        }
        if (substr($m[0], -1) === '+')
        {
            $this->param_names_path[$m[1]] = 1;
            return '(?P<' . $m[1] . '>.+)';
        }

        return '(?P<' . $m[1] . '>[^/]+)';
    }


    /**
     * Is this route specific to defined HTTP verbs
     */
    public function usesHttpVerbs()
    {
		return !empty($this->verbs);
    }

}
