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
namespace Drest\Service\Action;

use Drest\DrestException;

/**
 * Service action registry class
 * This is used to register custom service actions
 *
 */
class Registry
{
    /**
     * An array of action objects
     * @var array $actions
     */
    protected $actions = [];

    /**
     * An array of routes using the name as key, and corresponding offset (int) in $actions array
     * @var array $routes
     * - example ['User::get_user' => 0, 'User::get_user' => 0]
     */
    protected $routes = [];

    /**
     * Register service actions by their named route.
     * E.g. ->register($action, ['User::post_user', 'Address::post_address'])
     * @param AbstractAction $action
     * @param array $namedRoutes
     * @throws DrestException
     */
    public function register(AbstractAction $action, array $namedRoutes)
    {
        $this->actions[] = $action;
        $key = array_search($action, $this->actions);
        foreach ($namedRoutes as $route)
        {
            // Check the format
            $this->checkNamedRoute($route);
            $this->routes[$route] = $key;
        }
    }

    /**
     * Check the format of the named route
     * @param $namedRoute
     * @throws DrestException
     */
    protected function checkNamedRoute($namedRoute)
    {
        if (substr_count($namedRoute, '::') !== 1) {
            throw DrestException::invalidNamedRouteSyntax();
        }
        if (sizeof(explode('::', $namedRoute)) !== 2)
        {
            throw DrestException::invalidNamedRouteSyntax();
        }
    }

    /**
     * Unregister by action object
     * @param AbstractAction $action
     */
    public function unregisterByAction(AbstractAction $action)
    {
        if (($offset = array_search($action, $this->actions)) !== false)
        {
            unset($this->actions[$offset]);
            foreach ($this->routes as $key => $value)
            {
                if ($value === $offset)
                {
                    // Remove any routes registered with this action
                    unset($this->routes[$key]);
                }
            }
        }
    }

    /**
     * Unregister an action
     * @param \Drest\Mapping\RouteMetaData|string $route
     * - can either be a route metadata object, or a string 'User::get_user'
     */
    public function unregisterByRoute($route)
    {
        $routeName = ($route instanceof \Drest\Mapping\RouteMetaData)
            ? $route->getNamedRoute()
            : $route;

        $this->checkNamedRoute($routeName);

        if (isset($this->routes[$routeName]))
        {
            $actionOffset = $this->routes[$routeName];
            unset($this->routes[$routeName]);

            if (!in_array($actionOffset, $this->routes))
            {
                // It's no longer in the routes array, so we should remove it from actions
                unset($this->actions[$actionOffset]);
            }
        }
    }

    /**
     * Does this registry have a service action for this route
     * @param \Drest\Mapping\RouteMetaData $routeMetaData
     * @return bool
     */
    public function hasServiceAction(\Drest\Mapping\RouteMetaData $routeMetaData)
    {
        return array_key_exists($routeMetaData->getNamedRoute(), $this->routes);
    }

    /**
     * Get the service action class
     * @param \Drest\Mapping\RouteMetaData $routeMetaData
     * @return AbstractAction|null
     */
    public function getServiceAction(\Drest\Mapping\RouteMetaData $routeMetaData)
    {
        if (!$this->hasServiceAction($routeMetaData))
        {
            return null;
        }
        return $this->actions[$this->routes[$routeMetaData->getNamedRoute()]];
    }

}