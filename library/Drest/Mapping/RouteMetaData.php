<?php
namespace Drest\Mapping;

/**
 *
 * A class metadata instance that holds all the information for a Drest entity
 * @author Lee
 *
 */
use Drest\DrestException;

class RouteMetaData
{
	const CONTENT_TYPE_ELEMENT = 1;
	const CONTENT_TYPE_COLLECTION = 2;

	public static $contentTypes = array(
	    self::CONTENT_TYPE_ELEMENT => 'Element',
	    self::CONTENT_TYPE_COLLECTION => 'Collection'
	);

	/**
	 * This route objects parent
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
	 * @var array $route_params
	 */
	protected $route_params;

	/**
	 * An index array of URL parameters that exist but didn't match a route pattern parameter
	 * Eg: pattern: /user/:id+  with url: /user/1/some/additional/params. The value id => 1 will go into $route_params
	 * All the rest will go in here.
	 * @var array $route_params
	 */
	protected $unmapped_route_params;

	/**
	 * The route name (must be unique)
	 * @var string $name
	 */
	protected $name;

	/**
	 * Any array of verbs allowed on this route. They match the constant values defined in Drest\Request eg array('GET', 'POST')
	 * @var array $verbs
	 */
	protected $verbs;

	/**
	 * Content type to be used. Can either be a single entity element, or a collection. Contains value of respective constant
	 * @var integer $content_type
	 */
	protected $content_type;

	/**
	 * The call method to be called upon successful routing
	 * @var string $call_method
	 */
	protected $call_method;

	/**
	 * An array of fields to be exposed to the end client
	 * @var array $expose
	 */
	protected $expose = array();

	/**
	 * Whether this route is open to allow OPTION requests to detail available $verbs
	 * -1 = not set
	 * 0  = not allowed
	 * 1  = allowed
	 * @var integer $allowed_option_request;
	 */
	protected $allowed_option_request = -1;


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
	 * Get this routes route pattern
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
	 * Get the name of this route
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
	 * Get the content type to be used for this route
	 * @return string $content_type
	 */
	public function getContentType()
	{
	    return $this->content_type;
	}

	/**
	 * Sets the content type to be used for this route
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
	 * Get an array of verbs that are allowed on this route
	 * @return @array $verbs
	 */
	public function getVerbs()
	{
	    return $this->verbs;
	}

	/**
	 * Add verbs that are to be allowed on this route.
	 * @param mixed $verbs = a sinlge or array of verbs valid for this route. eg array('GET', 'PUT')
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
	 * Get the call method to be used
	 * @return string
	 */
	public function getCallMethod()
	{
	    return $this->call_method;
	}

	/**
	 * Get any params that were set after a sucessful match
	 * @return array $params
	 */
	public function getRouteParams()
	{
        return (!empty($this->route_params)) ? $this->route_params : array();
	}

	/**
	 * Get any unmapped route parameters
	 * @return array $params
	 */
	public function getUnmappedRouteParams()
	{
	    return $this->unmapped_route_params;
	}

	/**
	 * Set the call method (on the route class) to be used upon routing match
	 * @param string $call_method
	 */
	public function setCallMethod($call_method)
	{
	    $this->call_method = $call_method;
	}

	/**
	 * An array of fields we're allowed to expose to the client
	 * @param array $expose
	 */
	public function setExpose(array $expose)
	{
        $this->expose = $expose;
	}

	/**
	 * Get the field exposure on this route
	 * @reutrn array $expose
	 */
	public function getExpose()
	{
	    return $this->expose;
	}

	/**
	 * Set whether we would like to expose this route (and its verbs) to OPTIONS requests
	 * @param integer|boolean $value - if using integer -1 to unset, 0 for no and 1 if yes
	 */
	public function setAllowedOptionRequest($value = true)
	{
	    if (is_bool($value))
	    {
	        $this->allowed_option_request = ($value) ? 1 : 0;
	    } elseif ($value != -1)
	    {
	        throw new Exception('Invalid Allow Options value, must be -1 to unset, 0 for no or 1 for yes. Or you can use boolean values');
	    }
	    $this->allowed_option_request = $value;
	}

	/**
	 * Is this route allowed to expose its verbs to OPTIONS requests
	 * @return integer $result -1 if not set, 0 if no and 1 if yes
	 */
	public function isAllowedOptionRequest()
	{
        return $this->allowed_option_request;
	}

    /**
     * Does this request matches the route pattern
     * @param Drest\Request $request
     * @param boolean $matchVerb - Whether you want to match the route using the request HTTP verb - useful for OPTIONS requests
     * @return boolean $result
     */
    public function matches(\Drest\Request $request, $matchVerb = true)
    {
		if ($matchVerb && $this->usesHttpVerbs())
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
        if (!preg_match('#^' . $patternAsRegex . '$#', $request->getPath(), $paramValues))
        {
            return false;
        }

        foreach ($this->param_names as $name)
        {
            if (isset($paramValues[$name]))
            {
                if (isset($this->param_names_path[$name]))
                {
                    $parts = explode('/', urldecode($paramValues[$name]));
                    $this->route_params[$name] = array_shift($parts);
                    $this->unmapped_route_params = $parts;
                } else
                {
                    $this->route_params[$name] = urldecode($paramValues[$name]);
                }
            }
        }

        // Check the route conditions
        foreach ($this->route_conditions as $key => $condition)
        {
            if (!preg_match('/^' . $condition . '$/', $this->route_params[$key]))
            {
                return false;
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
