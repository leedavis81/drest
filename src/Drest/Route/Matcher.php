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
namespace Drest\Route;

use Drest\Mapping\RouteMetaData;
use DrestCommon\Request\Request;


/**
 * Class Matcher takes a request object and finds matching routes
 * @package Drest\Route
 */
class Matcher
{

    /**
     * The route meta data this matcher should operate on
     * @var RouteMetaData $routeMetaData
     */
    protected $routeMetaData;

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
     * Route meta data to test a match on
     * @param RouteMetaData|null $routeMetaData
     */
    public function __construct(RouteMetaData $routeMetaData = null)
    {
        if (!is_null($routeMetaData))
        {
            $this->setRouteMetaData($routeMetaData);
        }
    }

    /**
     * Set the route meta data
     * @param RouteMetaData $routeMetaData
     */
    public function setRouteMetaData(RouteMetaData $routeMetaData)
    {
        $this->routeMetaData = $routeMetaData;
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
        // If we're matching the verb and we've defined them, ensure the method used is in our list of registered verbs
        if ($matchVerb &&
            $this->routeMetaData->usesHttpVerbs() &&
            !$this->methodIsInOurListOfAllowedVerbs($request->getHttpMethod())) {
            return false;
        }

        $patternAsRegex = $this->getMatchRegexPattern($basePath);

        //Cache URL params' names and values if this route matches the current HTTP request
        if (!preg_match('#^' . $patternAsRegex . '$#', $request->getPath(), $paramValues)) {
            return false;
        }

        // Process the param names and save them on the route params
        $this->processRouteParams($paramValues);

        // Check the route conditions
        if (!$this->routeConditionsAreValid())
        {
            return false;
        }

        return true;
    }

    /**
     * Get the determined route parameters
     * @return array
     */
    public function getRouteParams()
    {
        return $this->route_params;
    }


    /**
     * Get the param names
     * @return array
     */
    public function getParamNames()
    {
        return $this->param_names;
    }

    /**
     * Get the params names path
     * @return array
     */
    public function getParamNamesPath()
    {
        return $this->param_names_path;
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
     * Get the regex pattern to match the request path
     * @param $basePath
     * @return string
     */
    protected function getMatchRegexPattern($basePath)
    {
        // Convert URL params into regex patterns, construct a regex for this route, init params
        $routePattern = (is_null($basePath))
            ? (string) $this->routeMetaData->getRoutePattern()
            : '/' . $basePath . '/' . ltrim((string) $this->routeMetaData->getRoutePattern(), '/');
        $patternAsRegex = preg_replace_callback(
            '#:([\w]+)\+?#',
            array($this, 'matchesCallback'),
            str_replace(')', ')?', $routePattern)
        );
        if (substr($this->routeMetaData->getRoutePattern(), -1) === '/') {
            $patternAsRegex .= '?';
        }
        return $patternAsRegex;
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
     * Process the route names and add them as parameters
     * @param array $paramValues
     */
    protected function processRouteParams(array $paramValues)
    {
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
    }

    /**
     * Are the given route conditions matching
     * @return bool
     */
    protected function routeConditionsAreValid()
    {
        foreach ($this->routeMetaData->getRouteConditions() as $key => $condition) {
            if (!preg_match('/^' . $condition . '$/', $this->route_params[$key])) {
                $this->param_names_path = $this->route_params = $this->unmapped_route_params = array();
                return false;
            }
        }
        return true;
    }

    /**
     * Ensure our method is in out list of allowed verbs
     * @param $httpMethod
     * @return bool
     */
    protected function methodIsInOurListOfAllowedVerbs($httpMethod)
    {
        if (!in_array($httpMethod, $this->routeMetaData->getVerbs())) {
            return false;
        }
        return true;
    }
}