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
	 * Key-value array of URL parameters populated after a match has been successful - or directly by using available setter
	 * @var array $route_params
	 */
	protected $route_params;

	/**
	 * An index array of URL parameters that exist but didn't match a route pattern parameter
	 * Eg: pattern: /user/:id+  with url: /user/1/some/additional/params. The value id => 1 will go into $route_params
	 * All the rest will go in here.
	 * @var array $unmapped_route_params
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
	 * Whether get requests to this route should be exposed / handled as collections
	 * @var boolean $collection
	 */
	protected $collection = false;

	/**
	 * The service call to be executed upon successful routing
	 * @var array $service_call_class
	 */
	protected $service_call_class;

	/**
	 * @var array $service_call_method
	 */
	protected $service_call_method;

	/**
	 * A handle function call for this route (if one is configured)
	 * @var string $handle_call
	 */
	protected $handle_call;

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
	    return $this->route_pattern;
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
	 * Whether requests to this route should be handled as collections
	 * @param boolean $value
	 */
	public function setCollection($value = true)
	{
        $this->collection = (bool) $value;
	}

	/**
	 * Should this route be handled as a collection
	 */
	public function isCollection()
	{
	    return (bool) $this->collection;
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
	 * Set the service call information (on the route class) to be used upon routing a match
	 * @param array $service_call - should be format array("CLASSNAME", "METHODNAME")
	 */
	public function setServiceCall(array $service_call)
	{
	    if (sizeof($service_call) !== 2)
	    {
	        throw DrestException::invalidServiceCallFormat();
	    }
	    $this->service_call_class = (!empty($service_call[0])) ? $service_call[0] : null;
	    $this->service_call_method = (!empty($service_call[1])) ? $service_call[1] : null;
	}

	/**
	 * Get the service call class name
	 * @return string $service_class
	 */
	public function getServiceCallClass()
	{
        return $this->service_call_class;
	}

	/**
	 * Get the service call method to be used
	 * @return string $service_call_method
	 */
	public function getServiceCallMethod()
	{
	    return $this->service_call_method;
	}


	/**
	 * Inject route params onto this object without performing a match. Useful when calling a named route directly
	 * @param array $params - should be an associative array. keyed values are ignored
	 */
	public function setRouteParams(array $params = array())
	{
	    $this->route_params = array_flip(array_filter(array_flip($params), function($entry){
	        return !is_int($entry);
	    }));
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
	 * Inject unmapped route params onto this object without performing a match. Useful when calling a named route directly
	 * @param array $params - should be a keyed array. associative values are ignored
	 */
	public function setUnmappedRouteParams(array $params = array())
	{
	    $this->unmapped_route_params = array_flip(array_filter(array_flip($params), function($entry){
	        return !is_string($entry);
	    }));
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
	 * Set the handle function call
	 * @param string $handle_call
	 */
	public function setHandleCall($handle_call)
	{
        if (is_string($handle_call))
        {
            $this->handle_call = $handle_call;
        }
	}

	/**
	 * Get the handle call function
	 * @return string $handle_call
	 */
	public function getHandleCall()
	{
	    return $this->handle_call;
	}

	/**
	 *
	 * Does this route have an annotated handle call
	 */
	public function hasHandleCall()
	{
	    return isset($this->handle_call);
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
	        throw DrestException::invalidAllowedOptionsValue();
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
     * @param string $basePath - add a base path to the route pattern
     * @return boolean $result
     */
    public function matches(\Drest\Request $request, $matchVerb = true, $basePath = null)
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
        $routePattern = (is_null($basePath)) ? (string) $this->route_pattern : '/' . $basePath . '/' . ltrim((string) $this->route_pattern, '/');
        $patternAsRegex = preg_replace_callback('#:([\w]+)\+?#', array($this, 'matchesCallback'), str_replace(')', ')?', $routePattern));
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
                $this->param_names_path = $this->route_params = $this->unmapped_route_params = array();
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
