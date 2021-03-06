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
namespace Drest;

use Drest\Mapping\RouteMetaData;
use Drest\Route\MultipleRoutesException;
use Drest\Route\NoMatchException;
use Drest\Service\Action\Registry as ServiceActionRegistry;
use DrestCommon\Error\Handler\AbstractHandler;
use DrestCommon\Error\Response\Text as ErrorResponseText;
use DrestCommon\Representation\UnableToMatchRepresentationException;
use DrestCommon\Request\Request;
use DrestCommon\Response\Response;

class Manager
{
    use HttpManagerTrait;
    use EventManagerTrait;

    /**
     * Doctrine Entity Manager Registry
     * @var EntityManagerRegistry $emr
     */
    protected $emr;

    /**
     * Drest configuration object
     * @var Configuration $config
     */
    protected $config;

    /**
     * Metadata manager object
     * @var Manager\Metadata $metadataManager
     */
    protected $metadataManager;

    /**
     * Representation manager
     * @var Manager\Representation
     */
    protected $representationManager;

    /**
     * Drest router
     * @var \Drest\Router $router
     */
    protected $router;

    /**
     * A service object used to handle service actions
     * @var Service $service
     */
    protected $service;

    /**
     * Error handler object
     * @var AbstractHandler $error_handler
     */
    protected $error_handler;

    /**
     * The name of an explicit route call
     * @var string $named_route
     */
    protected $named_route;

    /**
     * Optional route parameter that may have been passed
     * @var array $route_params
     */
    protected $route_params;

    /**
     * Creates an instance of the Drest Manager using the passed configuration object
     * Can also pass in a Event Manager instance
     *
     * @param EntityManagerRegistry   $entityManagerRegistry
     * @param Configuration           $config
     * @param Event\Manager           $eventManager
     * @param ServiceActionRegistry   $serviceActionRegistry
     */
    private function __construct(
        EntityManagerRegistry $entityManagerRegistry,
        Configuration $config,
        Event\Manager $eventManager,
        ServiceActionRegistry $serviceActionRegistry
    )
    {
        $this->emr = $entityManagerRegistry;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->service = new Service($this, $serviceActionRegistry);

        // Router is internal and currently cannot be injected / extended
        $this->router = new Router();

        $this->metadataManager = Manager\Metadata::create($config);
        $this->representationManager = Manager\Representation::create($this->config);
    }

    /**
     * Static call to create the Drest Manager instance
     *
     * @param  EntityManagerRegistry    $entityManagerRegistry
     * @param  Configuration            $config
     * @param  Event\Manager|null       $eventManager
     * @param  ServiceActionRegistry  $serviceActionRegistry
     * @return Manager                  $manager
     */
    public static function create(
        EntityManagerRegistry $entityManagerRegistry,
        Configuration $config,
        Event\Manager $eventManager = null,
        ServiceActionRegistry $serviceActionRegistry = null
    )
    {
        $driver = $config->getMetadataDriverClass();

        if(method_exists($driver, 'register')) {
            $driver::register($config);
        }

        return new self(
            $entityManagerRegistry,
            $config,
            ($eventManager) ?: new Event\Manager(),
            ($serviceActionRegistry) ?: new Service\Action\Registry()
        );
    }


    /**
     * Dispatch a REST request
     * @param  object     $request     - Framework request object
     * @param  object     $response    - Framework response object
     * @param  string     $namedRoute  - Define the named Route to be dispatch - by passes the internal router
     * @param  array      $routeParams - Route parameters to be used when dispatching a namedRoute request
     * @return Response   $response - returns a Drest response object which can be sent calling toString()
     * @throws \Exception - Upon failure
     */
    public function dispatch($request = null, $response = null, $namedRoute = null, array $routeParams = [])
    {
        $this->setUpHttp($request, $response, $this->getConfiguration());

        // Save the named route to the object (overwritten on each dispatch)
        $this->named_route = $namedRoute;
        $this->route_params = $routeParams;

        // Register routes for lookup
        $this->metadataManager->registerRoutes($this->router);

        // trigger preDispatch event
        $this->triggerPreDispatchEvent($this->service);

        try {
            $this->execute();
        } catch (\Exception $e) {

            if ($this->config->inDebugMode()) {
                // trigger a postDispatch event
                $this->triggerPostDispatchEvent($this->service);
                throw $e;
            }
            $this->handleError($e);
        }

        // trigger a postDispatch event
        $this->triggerPostDispatchEvent($this->service);

        return $this->getResponse();
    }

    /**
     * Execute a dispatched request
     * @throws Route\NoMatchException|\Exception
     */
    protected function execute()
    {
        if (($route = $this->determineRoute()) instanceof RouteMetaData) {

            // Set the matched service object and the error handler into the service class
            $this->service->setMatchedRoute($route);
            // Get the representation to be used - always successful or it throws an exception
            $this->service->setRepresentation($this->representationManager->handleExposureSettingsFromHttpMethod(
                $this->getRequest(),
                $route,
                $this->emr
            ));
            $this->service->setErrorHandler($this->getErrorHandler());

            // Set up the service for a new request
            $this->service->setUpAndRunRequest();
        }
    }


    /**
     * Determine the matched route from either the router or namedRoute
     * Returns false no route could be matched (ideally the response should be returned in this instance - fail fast)
     * @throws Route\NoMatchException|\Exception
     * @return RouteMetaData|false $route
     */
    protected function determineRoute()
    {
        // dispatch preRoutingAction event
        $this->triggerPreRoutingEvent($this->service);
        try {
            $route = (!is_null($this->named_route))
                ? $this->getNamedRoute()
                : $this->getMatchedRoute(true);
        } catch (\Exception $e) {

            // dispatch postRoutingAction event
            $this->triggerPostRoutingEvent($this->service);

            if ($e instanceof NoMatchException &&
                ($this->doCGOptionsCheck() || $this->doOptionsCheck())
            ) {
                return false;
            }
            throw $e;
        }

        // check push edges have a handle, or a registered service action (also, only do this when we match on them)
        if ($route->needsHandleCall() &&
                (!$route->hasHandleCall() &&
                 !$this->service->getServiceActionRegistry()->hasServiceAction($route))
        )
        {
            throw DrestException::routeRequiresHandle($route->getName());
        }


        // dispatch postRoutingAction event
        $this->triggerPostRoutingEvent($this->service);

        // Set parameters matched on the route to the request object
        $this->getRequest()->setRouteParam($route->getRouteParams());

        return $route;
    }

    /**
     * Get a route based on Entity::route_name. eg Entities\User::get_users
     * Syntax checking is performed
     * @throws DrestException on invalid syntax or unmatched named route
     * @return RouteMetaData  $route
     */
    protected function getNamedRoute()
    {
        if (substr_count($this->named_route, '::') !== 1) {
            throw DrestException::invalidNamedRouteSyntax();
        }
        $parts = explode('::', $this->named_route);

        // Allow exception to bubble up
        $classMetaData = $this->getClassMetadata($parts[0]);
        if (($route = $classMetaData->getRouteMetaData($parts[1])) === false) {
            throw DrestException::unableToFindRouteByName($parts[1], $classMetaData->getClassName());
        }

        $route->setRouteParams($this->route_params);

        return $route;
    }

    /**
     * Was the last dispatch request called with a named route?
     * @return bool
     */
    public function calledWithANamedRoute()
    {
        return !is_null($this->named_route);
    }

    /**
     * Check if the client has requested the CG classes with an OPTIONS call
     * @return bool
     */
    protected function doCGOptionsCheck()
    {
        $genClasses = $this->getRequest()->getHeaders(ClassGenerator::HEADER_PARAM);
        if ($this->getRequest()->getHttpMethod() != Request::METHOD_OPTIONS || empty($genClasses)) {
            return false;
        }

        $classGenerator = new ClassGenerator($this->emr);

        $classMetadatas = [];
        if (!empty($genClasses)) {
            foreach ($this->metadataManager->getAllClassNames() as $className) {
                $metaData = $this->getClassMetadata($className);
                foreach ($metaData->getRoutesMetaData() as $route) {
                    /* @var RouteMetaData $route */
                    $route->setExpose(
                        Query\ExposeFields::create($route)
                            ->configureExposeDepth(
                                $this->emr,
                                $this->config->getExposureDepth(),
                                $this->config->getExposureRelationsFetchType()
                            )
                            ->toArray()
                    );
                }
                $classMetadatas[] = $metaData;
            }
        }

        $classGenerator->create($classMetadatas);

        $this->getResponse()->setBody($classGenerator->serialize());

        return true;
    }

    /**
     * No match on route has occurred. Check the HTTP verb used for an options response
     * Returns true if it is, and option information was successfully written to the response object
     * @return boolean $success
     */
    protected function doOptionsCheck()
    {
        if ($this->getRequest()->getHttpMethod() != Request::METHOD_OPTIONS) {
            return false;
        }

        // Do a match on all routes - don't include a verb check
        $verbs = [];
        foreach ($this->getMatchedRoutes(false) as $route) {
            /* @var RouteMetaData $route */
            $allowedOptions = $route->isAllowedOptionRequest();
            if (false === (($allowedOptions === -1)
                    ? $this->config->getAllowOptionsRequest()
                    : (bool) $allowedOptions)
            ) {
                continue;
            }
            $verbs = array_merge($verbs, $route->getVerbs());
        }

        if (empty($verbs)) {
            return false;
        }

        $this->getResponse()->setHttpHeader('Allow', implode(', ', $verbs));

        return true;
    }

    /**
     * Runs through all the registered routes and returns a single match
     * @param  boolean                 $matchVerb - Whether you want to match the route using the request HTTP verb
     * @throws NoMatchException        if no routes are found
     * @throws MultipleRoutesException If there are multiple matches
     * @return RouteMetaData           $route
     */
    protected function getMatchedRoute($matchVerb = true)
    {
        // Inject any route base Paths that have been registered
        if ($this->config->hasRouteBasePaths()) {
            $this->router->setRouteBasePaths($this->config->getRouteBasePaths());
        }

        $matchedRoutes = $this->router->getMatchedRoutes($this->getRequest(), (bool) $matchVerb);
        if (sizeof($matchedRoutes) == 0) {
            throw NoMatchException::noMatchedRoutes();
        } elseif (sizeof($matchedRoutes) > 1) {
            throw MultipleRoutesException::multipleRoutesFound($matchedRoutes);
        }

        return $matchedRoutes[0];
    }

    /**
     * Get all possible match routes for this request
     * @param  boolean $matchVerb - Whether you want to match the route using the request HTTP verb
     * @return array   of Drest\Mapping\RouteMetaData object
     */
    protected function getMatchedRoutes($matchVerb = true)
    {
        return $this->router->getMatchedRoutes($this->getRequest(), (bool) $matchVerb);
    }

    /**
     * Handle an error by passing the exception to the registered error handler
     * @param  \Exception $e
     * @throws \Exception
     */
    private function handleError(\Exception $e)
    {
        $eh = $this->getErrorHandler();

        try {
            $representation = $this->representationManager->getDeterminedRepresentation($this->getRequest());
            $errorDocument = $representation->getDefaultErrorResponse();
            $eh->error($e, 500, $errorDocument);
        } catch (UnableToMatchRepresentationException $e) {
            $errorDocument = new ErrorResponseText();
            $eh->error($e, 500, $errorDocument);
        }

        $this->getResponse()->setStatusCode($eh->getResponseCode());
        $this->getResponse()->setHttpHeader('Content-Type', $errorDocument::getContentType());
        $this->getResponse()->setBody($errorDocument->render());
    }

    /**
     * Get Configuration object used
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * Get the entity manager registry
     * @return EntityManagerRegistry
     */
    public function getEntityManagerRegistry()
    {
        return $this->emr;
    }

    /**
     * Get the service action registry
     * @return ServiceActionRegistry
     */
    public function getServiceActionRegistry()
    {
        return $this->service->getServiceActionRegistry();
    }

    /**
     * Get the error handler object, if none has been injected use default from config
     * @return AbstractHandler $error_handler
     */
    public function getErrorHandler()
    {
        if (!$this->error_handler instanceof AbstractHandler) {
            // Force creation of an instance of the default error handler
            $className = $this->config->getDefaultErrorHandlerClass();
            $this->error_handler = new $className();
        }

        return $this->error_handler;
    }

    /**
     * Set the error handler to use
     * @param AbstractHandler $error_handler
     */
    public function setErrorHandler(AbstractHandler $error_handler)
    {
        $this->error_handler = $error_handler;
    }

    /**
     * Get metadata for an entity class
     * @param  string                   $className
     * @return Mapping\ClassMetaData
     */
    public function getClassMetadata($className)
    {
        return $this->metadataManager->getMetadataForClass($className);
    }

    /**
     * Iterates through mapping definitions, any exceptions thrown will bubble up.
     */
    public function checkDefinitions()
    {
        foreach ($this->metadataManager->getAllClassNames() as $class) {
            $this->getClassMetadata($class);
        }
    }
}
