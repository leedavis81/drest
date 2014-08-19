<?php
namespace Drest\Mapping;

use Doctrine\ORM\EntityManager;
use Drest\DrestException;
use DrestCommon\Request\Request;

/**
 *
 * A class metadata instance that holds all the information for a Drest entity
 * @author Lee
 *
 */
class RouteMetaData implements \Serializable
{
    /**
     * This route objects parent
     * @var ClassMetaData $classMetaData
     */
    protected $class_metadata;

    /**
     * A string route pattern to be matched on. eg /user/:id
     * @var string $route_pattern
     */
    protected $route_pattern;

    /**
     * Any custom regex conditions that are needed when matching a route param component.
     * Eg array('year' => '(19|20)\d\d')
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
     * - or directly by using available setter
     * @var array $route_params
     */
    protected $route_params;

    /**
     * An index array of URL parameters that exist but didn't match a route pattern parameter
     * Eg: pattern: /user/:id+  with url: /user/1/some/additional/params.
     * The value id => 1 will go into $route_params
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
     * Any array of verbs allowed on this route.
     * They match the constant values defined in DrestCommon\Request\Request eg array('GET', 'POST')
     * @var array $verbs
     */
    protected $verbs;

    /**
     * Whether get requests to this route should be exposed / handled as collections
     * @var boolean $collection
     */
    protected $collection = false;

    /**
     * The action class to be executed upon successful routing
     * @var string $action_class
     */
    protected $action_class;

    /**
     * A handle function call for this route (if one is configured)
     * @var string $handle_call
     */
    protected $handle_call;

    /**
     * Whether to inject a DrestCommon\Request\Request object into the handle method
     * @var bool $inject_request_into_handle
     */
    protected $inject_request_into_handle = false;

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
     * @var integer $allowed_option_request ;
     */
    protected $allowed_option_request = -1;


    /**
     * Set this objects parent metadata class
     * @param ClassMetaData $class_metadata
     */
    public function setClassMetaData(ClassMetaData $class_metadata)
    {
        $this->class_metadata = $class_metadata;
    }

    /**
     * Get this classes metadata object
     * @return ClassMetaData $class_metadata
     */
    public function getClassMetaData()
    {
        return $this->class_metadata;
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
     * @param string $route_pattern
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
        foreach ($route_conditions as $field => $condition) {
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
     * Sets a unique reference name for the resource.
     * If other resources are created with this name an exception is thrown (must be unique)
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
     * @return array
     */
    public function getVerbs()
    {
        return $this->verbs;
    }

    /**
     * Add verbs that are to be allowed on this route.
     * @param  mixed          $verbs = a single or array of verbs valid for this route. eg array('GET', 'PUT')
     * @throws DrestException if verb is invalid
     */
    public function setVerbs($verbs)
    {
        $verbs = (array) $verbs;
        foreach ($verbs as $verb) {
            $verb = strtoupper($verb);
            if (!defined('DrestCommon\Request\Request::METHOD_' . $verb)) {
                throw DrestException::invalidHttpVerbUsed($verb);
            }
            $this->verbs[] = $verb;
        }
    }

    /**
     * Set the action class to be executing upon routing a match
     * @param string $action_class - class name (can include namespace)
     */
    public function setActionClass($action_class)
    {
        $this->action_class = $action_class;
    }

    /**
     * Get the action class name
     * @return string $action_class
     */
    public function getActionClass()
    {
        return $this->action_class;
    }


    /**
     * Inject route params onto this object without performing a match. Useful when calling a named route directly
     * @param array $params - should be an associative array. keyed values are ignored
     */
    public function setRouteParams(array $params = array())
    {
        $this->route_params = array_flip(
            array_filter(
                array_flip($params),
                function ($entry) {
                    return !is_int($entry);
                }
            )
        );
    }

    /**
     * Get any params that were set after a successful match
     * @return array $params
     */
    public function getRouteParams()
    {
        return (!empty($this->route_params)) ? $this->route_params : array();
    }

    /**
     * Inject unmapped route params onto this object without performing a match.
     * Useful when calling a named route directly
     * @param array $params - should be a keyed array. associative values are ignored
     */
    public function setUnmappedRouteParams(array $params = array())
    {
        $this->unmapped_route_params = array_flip(
            array_filter(
                array_flip($params),
                function ($entry) {
                    return !is_string($entry);
                }
            )
        );
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
     * @return array $expose
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
        if (is_string($handle_call)) {
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
     * Does this route need a handle call? Required for POST/PUT/PATCH verbs
     * @return boolean $response
     */
    public function needsHandleCall()
    {
        foreach ($this->verbs as $verb) {
            switch ($verb) {
                case Request::METHOD_POST:
                case Request::METHOD_PUT:
                case Request::METHOD_PATCH:
                    return true;
                    break;
            }
        }

        return false;
    }

    /**
     * Set to inject the DrestCommon\Request\Request object into a handle method
     * @param bool $setting
     */
    public function setInjectRequestIntoHandle($setting)
    {
        $this->inject_request_into_handle = (bool) $setting;
    }

    /**
     * Do we need to inject the request object into the handle call
     * @return bool
     */
    public function getInjectRequestIntoHandle()
    {
        return $this->inject_request_into_handle;
    }

    /**
     * Set whether we would like to expose this route (and its verbs) to OPTIONS requests
     * @param  integer|boolean $value - if using integer -1 to unset, 0 for no and 1 if yes
     * @throws DrestException
     */
    public function setAllowedOptionRequest($value = true)
    {
        if (is_bool($value)) {
            $this->allowed_option_request = ($value) ? 1 : 0;
        } elseif ($value != -1) {
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
     * Get the origin route (it could be this instance)
     * @param  EntityManager      $em - Optionally pass the entity manager to assist in determining a GET origin location
     * @return null|RouteMetaData $route
     */
    public function getOriginRoute(EntityManager $em = null)
    {
        return $this->class_metadata->getOriginRoute($em);
    }

    /**
     * Generate the location string from the provided object
     * @param  object        $object
     * @param  string        $url    - the Url to be prepended to the location
     * @param  EntityManager $em     - Optionally pass the entity manager to assist in determining a GET origin location
     * @return string|false
     */
    public function getOriginLocation($object, $url, EntityManager $em = null)
    {
        $exposedObjectArray = self::getObjectVarsArray($object);
        if (($route = $this->getOriginRoute($em)) !== null) {
            if (!is_null($em)) {
                $pattern = $route->getRoutePattern();
                $ormClassMetadata = $em->getClassMetadata($this->getClassMetaData()->getClassName());
                foreach ($ormClassMetadata->getIdentifierFieldNames() as $identifier) {
                    if (isset($exposedObjectArray[$identifier])) {
                        $pattern = str_replace(':' . $identifier, $exposedObjectArray[$identifier], $pattern);
                    }
                }

                return $url . '/' . ltrim($pattern, '/');
            }
        }

        return false;
    }

    /**
     * Get an objects variables (including private / protected) as an array
     * @param $object
     * @throws \InvalidArgumentException
     * @return array
     */
    public static function getObjectVarsArray($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('To extract variables from an object, you must supply an object.');
        }

        $objectArray = (array) $object;
        $out = json_encode($objectArray);
        $out = preg_replace('/\\\u0000[*a-zA-Z_\x7f-\xff\\\][a-zA-Z0-9_\x7f-\xff\\\]*\\\u0000/', '', $out);

        return json_decode($out, true);
    }

    /**
     * Does this request matches the route pattern
     * @param  Request $request
     * @param  boolean $matchVerb - Whether you want to match the route using the request HTTP verb
     *                            - useful for OPTIONS requests
     * @param  string  $basePath  - add a base path to the route pattern
     * @return boolean $result
     */
    public function matches(Request $request, $matchVerb = true, $basePath = null)
    {
        if ($matchVerb && $this->usesHttpVerbs()) {
            try {
                $method = $request->getHttpMethod();
                if (!in_array($method, $this->verbs)) {
                    return false;
                }
            } catch (DrestException $e) {
                return false;
            }
        }

        //Convert URL params into regex patterns, construct a regex for this route, init params
        $routePattern = (is_null($basePath))
            ? (string) $this->route_pattern
            : '/' . $basePath . '/' . ltrim((string) $this->route_pattern, '/');
        $patternAsRegex = preg_replace_callback(
            '#:([\w]+)\+?#',
            array($this, 'matchesCallback'),
            str_replace(')', ')?', $routePattern)
        );
        if (substr($this->route_pattern, -1) === '/') {
            $patternAsRegex .= '?';
        }

        //Cache URL params' names and values if this route matches the current HTTP request
        if (!preg_match('#^' . $patternAsRegex . '$#', $request->getPath(), $paramValues)) {
            return false;
        }

        foreach ($this->param_names as $name) {
            if (isset($paramValues[$name])) {
                if (isset($this->param_names_path[$name])) {
                    $parts = explode('/', urldecode($paramValues[$name]));
                    $this->route_params[$name] = array_shift($parts);
                    $this->unmapped_route_params = $parts;
                } else {
                    $this->route_params[$name] = urldecode($paramValues[$name]);
                }
            }
        }

        // Check the route conditions
        foreach ($this->route_conditions as $key => $condition) {
            if (!preg_match('/^' . $condition . '$/', $this->route_params[$key])) {
                $this->param_names_path = $this->route_params = $this->unmapped_route_params = array();

                return false;
            }
        }

        return true;
    }

    /**
     * Convert a URL parameter (e.g. ":id", ":id+") into a regular expression
     * @param array - url parameters
     * @return string - Regular expression for URL parameter
     */
    protected function matchesCallback($m)
    {
        $this->param_names[] = $m[1];

        if (substr($m[0], -1) === '+') {
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

    /**
     * Serialise this object
     * @return string
     */
    public function serialize()
    {
        $trace = debug_backtrace();
        if (!isset($trace[2]) || $trace[2]['class'] != 'Drest\Mapping\ClassMetaData') {
            trigger_error('RouteMetaData can only be serialized from a parent instance of ClassMetaData', E_USER_ERROR);
        }

        return serialize(
            array(
                $this->route_pattern,
                $this->route_conditions,
                $this->param_names,
                $this->param_names_path,
                $this->route_params,
                $this->unmapped_route_params,
                $this->name,
                $this->verbs,
                $this->collection,
                $this->action_class,
                $this->handle_call,
                $this->inject_request_into_handle,
                $this->expose,
                $this->allowed_option_request
            )
        );
    }

    /**
     * Un-serialise this object and reestablish it's state
     */
    public function unserialize($string)
    {
        list(
            $this->route_pattern,
            $this->route_conditions,
            $this->param_names,
            $this->param_names_path,
            $this->route_params,
            $this->unmapped_route_params,
            $this->name,
            $this->verbs,
            $this->collection,
            $this->action_class,
            $this->handle_call,
            $this->inject_request_into_handle,
            $this->expose,
            $this->allowed_option_request
            ) = unserialize($string);
    }
}
