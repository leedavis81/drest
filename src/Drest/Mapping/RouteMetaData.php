<?php
/**
 * This file is part of the Drest package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Lee Davis
 * @copyright Copyright (c) Lee Davis <@leedavis81>
 * @link https://github.com/leedavis81/drest/blob/master/LICENSE
 * @license http://opensource.org/licenses/MIT The MIT X License (MIT)
 */
namespace Drest\Mapping;

use Doctrine\ORM\EntityManager;
use Drest\DrestException;
use Drest\Helper\ObjectToArray;
use Drest\Route\Matcher as RouteMatcher;
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
    protected $route_conditions = [];

    /**
     * Key-value array of URL parameter names
     * @var array $param_names
     */
    protected $param_names = [];

    /**
     * Key-value array of URL parameters with + at the end
     * @var array $param_names_path
     */
    protected $param_names_path = [];

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
     * A handle function call for this route (if one is configured)
     * @var string $handle_call
     */
    protected $handle_call;

    /**
     * An array of fields to be exposed to the end client
     * @var array $expose
     */
    protected $expose = [];

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
     * @param array $route_conditions
     */
    public function setRouteConditions(array $route_conditions)
    {
        foreach ($route_conditions as $field => $condition) {
            $this->route_conditions[preg_replace("/[^a-zA-Z0-9_]+/", "", $field)] = $condition;
        }
    }

    /**
     * Get the route conditions
     * @return array|null
     */
    public function getRouteConditions()
    {
        return $this->route_conditions;
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
     * The unique named route to reference this route by
     * Note that is should use the entities fully qualified class names
     * @return string
     */
    public function getNamedRoute()
    {
        return ltrim($this->getClassMetaData()->getClassName(), '\\') . '::' . $this->getName();
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
        foreach ((array) $verbs as $verb) {
            $verb = strtoupper($verb);
            if (!defined('DrestCommon\Request\Request::METHOD_' . $verb)) {
                throw DrestException::invalidHttpVerbUsed($verb);
            }
            $this->verbs[] = $verb;
        }
    }

    /**
     * Inject route params onto this object without performing a match. Useful when calling a named route directly
     * @param array $params - should be an associative array. keyed values are ignored
     */
    public function setRouteParams(array $params = [])
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
        return (!empty($this->route_params)) ? $this->route_params : [];
    }

    /**
     * Inject unmapped route params onto this object without performing a match.
     * Useful when calling a named route directly
     * @param array $params - should be a keyed array. associative values are ignored
     */
    public function setUnmappedRouteParams(array $params = [])
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
        foreach ($this->getVerbs() as $verb) {
            switch ($verb) {
                case Request::METHOD_POST:
                case Request::METHOD_PUT:
                case Request::METHOD_PATCH:
                    return true;
            }
        }

        return false;
    }

    /**
     * Set whether we would like to expose this route (and its verbs) to OPTIONS requests
     * @param  integer|boolean $value - if using integer -1 to unset, 0 for no and 1 if yes
     * @throws DrestException
     */
    public function setAllowedOptionRequest($value = true)
    {
        if (is_bool($value)) {
            $this->allowed_option_request = ((bool) $value) ? 1 : 0;
        }

        // No need to test for -1 value, it cannot be anything else at this point.

        // Value is converted and saved as an int.
        $this->allowed_option_request = (int) $value;
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
     * Generate the location string from the provided object
     * @param  object        $object
     * @param  string        $url    - the Url to be prepended to the location
     * @param  EntityManager $em     - Optionally pass the entity manager to assist in determining a GET origin location
     * @return string|false
     */
    public function getOriginLocation($object, $url, EntityManager $em = null)
    {
        $exposedObjectArray = ObjectToArray::execute($object);
        if (($route = $this->class_metadata->getOriginRoute($em)) !== null) {
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
     * Does this request match the route pattern
     * @param  Request $request
     * @param  boolean $matchVerb - Whether you want to match the route using the request HTTP verb
     *                            - useful for OPTIONS requests to provide route info
     * @param  string  $basePath  - add a base path to the route pattern
     * @return boolean $result
     */
    public function matches(Request $request, $matchVerb = true, $basePath = null)
    {
        $matcher = new RouteMatcher($this);
        if (!$matcher->matches($request, $matchVerb, $basePath))
        {
            return false;
        }

        // set determined parameters from running the match
        $this->unmapped_route_params = $matcher->getUnmappedRouteParams();
        $this->param_names = $matcher->getParamNames();
        $this->param_names_path = $matcher->getParamNamesPath();
        $this->route_params = $matcher->getRouteParams();

        return true;
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
            [
                $this->route_pattern,
                $this->route_conditions,
                $this->param_names,
                $this->param_names_path,
                $this->route_params,
                $this->unmapped_route_params,
                $this->name,
                $this->verbs,
                $this->collection,
                $this->handle_call,
                $this->expose,
                $this->allowed_option_request
            ]
        );
    }

    /**
     * Un-serialise this object and reestablish it's state
     * @param string $string
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
            $this->handle_call,
            $this->expose,
            $this->allowed_option_request
            ) = unserialize($string);
    }
}
