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
use DrestCommon\Error\Handler\AbstractHandler;
use DrestCommon\Error\Response\Text as ErrorResponseText;
use DrestCommon\Representation\AbstractRepresentation;
use DrestCommon\Representation\RepresentationException;
use DrestCommon\Representation\UnableToMatchRepresentationException;
use DrestCommon\Request\Request;
use DrestCommon\Response\Response;

class Manager
{
    use HttpManagerTrait;

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
     * Event manager object
     * @var Event\Manager
     */
    protected $eventManager;

    /**
     * Metadata manager object
     * @var \Drest\Manager\Metadata $metadataManager
     */
    protected $metadataManager;

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
     * Creates an instance of the Drest Manager using the passed configuration object
     * Can also pass in a Event Manager instance
     *
     * @param EntityManagerRegistry   $entityManagerRegistry
     * @param Configuration           $config
     * @param Event\Manager           $eventManager
     */
    private function __construct(
        EntityManagerRegistry $entityManagerRegistry,
        Configuration $config,
        Event\Manager $eventManager)
    {
        $this->emr = $entityManagerRegistry;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->service = new Service($this);

        // Router is internal and currently cannot be injected / extended
        $this->router = new Router();

        $this->metadataManager = new \Drest\Manager\Metadata($config);
    }

    /**
     * Static call to create the Drest Manager instance
     *
     * @param  EntityManagerRegistry    $entityManagerRegistry
     * @param  Configuration            $config
     * @param  Event\Manager|null       $eventManager
     * @return Manager                  $manager
     */
    public static function create(
        EntityManagerRegistry $entityManagerRegistry,
        Configuration $config,
        Event\Manager $eventManager = null)
    {
        // Register the annotations classes
        Mapping\Driver\AnnotationDriver::registerAnnotations();

        if ($eventManager === null) {
            $eventManager = new Event\Manager();
        }

        return new self($entityManagerRegistry, $config, $eventManager);
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
    public function dispatch($request = null, $response = null, $namedRoute = null, array $routeParams = array())
    {
        $this->setUpHttp($request, $response, $this->getConfiguration());

        // Register routes for lookup
        $this->metadataManager->registerRoutes($this->router);

        // trigger preDispatch event
        $this->getEventManager()->dispatchEvent(
            Event\Events::PRE_DISPATCH,
            new Event\PreDispatchArgs($this->service)
        );

        $rethrowException = false;
        try {
            $this->execute($namedRoute, $routeParams);
        } catch (\Exception $e) {

            if ($this->config->inDebugMode()) {
                $rethrowException = $e;
            } else {
                $this->handleError($e);
            }
        }

        // trigger a postDispatch event
        $this->getEventManager()->dispatchEvent(
            Event\Events::POST_DISPATCH,
            new Event\PostDispatchArgs($this->service)
        );

        if ($rethrowException) {
            throw $rethrowException;
        }

        return $this->getResponse();
    }

    /**
     * Execute a dispatched request
     * @param  string           $namedRoute  - Define the named Route to be dispatched - bypasses the internal router lookup
     * @param  array            $routeParams - Route parameters to be used for dispatching a namedRoute request
     * @throws Route\NoMatchException|\Exception
     */
    protected function execute($namedRoute = null, array $routeParams = array())
    {
        if (($route = $this->determineRoute($namedRoute, $routeParams)) instanceof RouteMetaData) {
            // Get the representation to be used - always successful or it throws an exception
            $representation = $this->getDeterminedRepresentation($route);

            // Configure push / pull exposure fields
            switch ($this->getRequest()->getHttpMethod()) {
                // Match on content option
                case Request::METHOD_GET:
                    $this->handlePullExposureConfiguration($route);
                    break;
                // Match on content-type
                case Request::METHOD_POST:
                case Request::METHOD_PUT:
                case Request::METHOD_PATCH:
                    $representation = $this->handlePushExposureConfiguration($route, $representation);
                    break;
            }

            // Set the matched service object and the error handler into the service class
            $this->service->setMatchedRoute($route);
            $this->service->setRepresentation($representation);
            $this->service->setErrorHandler($this->getErrorHandler());

            // Set up the service for a new request
            if ($this->service->setupRequest()) {
                $this->service->runCallMethod();
            }
        }
    }

    /**
     * Determine the matched route from either the router or namedRoute
     * @param  string|null                       $namedRoute
     * @param  array                             $routeParams
     * @throws Route\NoMatchException|\Exception
     * @return RouteMetaData|bool                $route - if false no route could be matched
     *                                                       (ideally the response should be returned in this instance - fail fast)
     */
    protected function determineRoute($namedRoute = null, array $routeParams = array())
    {
        // dispatch preRoutingAction event
        $this->getEventManager()->dispatchEvent(Event\Events::PRE_ROUTING, new Event\PreRoutingArgs($this->service));
        try {
            $route = (!is_null($namedRoute))
                ? $this->getNamedRoute($namedRoute, $routeParams)
                : $this->getMatchedRoute(true);
        } catch (\Exception $e) {
            // dispatch postRoutingAction event
            $this->getEventManager()->dispatchEvent(
                Event\Events::POST_ROUTING,
                new Event\PostRoutingArgs($this->service)
            );
            if ($e instanceof NoMatchException &&
                ($this->doCGOptionsCheck() || $this->doOptionsCheck())
            ) {
                return false;
            }
            throw $e;
        }
        // dispatch postRoutingAction event
        $this->getEventManager()->dispatchEvent(Event\Events::POST_ROUTING, new Event\PostRoutingArgs($this->service));

        // Set parameters matched on the route to the request object
        $this->getRequest()->setRouteParam($route->getRouteParams());

        return $route;
    }

    /**
     * Get a route based on Entity::route_name. eg Entities\User::get_users
     * Syntax checking is performed
     * @param  string         $name
     * @param  array          $params
     * @throws DrestException on invalid syntax or unmatched named route
     * @return RouteMetaData  $route
     */
    protected function getNamedRoute($name, array $params = array())
    {
        if (substr_count($name, '::') !== 1) {
            throw DrestException::invalidNamedRouteSyntax();
        }
        $parts = explode('::', $name);

        // Allow exception to bubble up
        $classMetaData = $this->getClassMetadata($parts[0]);
        if (($route = $classMetaData->getRouteMetaData($parts[1])) === false) {
            throw DrestException::unableToFindRouteByName($parts[1], $classMetaData->getClassName());
        }

        $route->setRouteParams($params);

        return $route;
    }

    /**
     * Handle a pull requests' exposure configuration (GET)
     * @param RouteMetaData $route (referenced object)
     */
    protected function handlePullExposureConfiguration(RouteMetaData &$route)
    {
        $route->setExpose(
            Query\ExposeFields::create($route)
                ->configureExposeDepth(
                    $this->emr,
                    $this->config->getExposureDepth(),
                    $this->config->getExposureRelationsFetchType()
                )
                ->configurePullRequest($this->config->getExposeRequestOptions(), $this->getRequest())
                ->toArray()
        );
    }

    /**
     * Handle a push requests' exposure configuration (POST/PUT/PATCH)
     * @param  RouteMetaData          $route          - the matched route
     * @param  AbstractRepresentation $representation - the representation class to be used
     * @return AbstractRepresentation $representation
     */
    protected function handlePushExposureConfiguration(RouteMetaData $route, AbstractRepresentation $representation)
    {
        $representation = $representation::createFromString($this->getRequest()->getBody());
        // Write the filtered expose data
        $representation->write(
            Query\ExposeFields::create($route)
                ->configureExposeDepth(
                    $this->emr,
                    $this->config->getExposureDepth(),
                    $this->config->getExposureRelationsFetchType()
                )
                ->configurePushRequest($representation->toArray())
        );

        return $representation;
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

        $classMetadatas = array();
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
        $verbs = array();
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
     * Detect an instance of a representation class using a matched route, or default representation classes
     * @param  RouteMetaData                        $route
     * @param  Mapping\RouteMetaData                $route
     * @throws UnableToMatchRepresentationException
     * @throws RepresentationException              - if unable to instantiate a representation object from config settings
     * @return AbstractRepresentation               $representation
     */
    protected function getDeterminedRepresentation(Mapping\RouteMetaData &$route = null)
    {
        $representations = (is_null($route) || array() === $route->getClassMetaData()->getRepresentations())
            ? $this->config->getDefaultRepresentations()
            : $route->getClassMetaData()->getRepresentations();

        if (empty($representations)) {
            $name = (is_null($route)) ? '"unknown name"' : $route->getName();
            $className = (is_null($route)) ? '"unknown class"' : $route->getClassMetaData()->getClassName();
            throw RepresentationException::noRepresentationsSetForRoute(
                $name,
                $className
            );
        }

        if (($representation = $this->searchAndValidateRepresentations($representations)) !== null) {
            return $representation;
        }

        // We have no representation instances from either annotations or config object
        throw UnableToMatchRepresentationException::noMatch();
    }

    /**
     * Iterate through an array of representations and return a match
     * @param array $representations
     * @return AbstractRepresentation|null
     * @throws RepresentationException
     * @throws UnableToMatchRepresentationException
     */
    protected function searchAndValidateRepresentations(array $representations)
    {
        $representationObjects = array();
        foreach ($representations as $representation) {
            if (!is_object($representation)) {
                // Check if the class is namespaced, if so instantiate from root
                $className = (strstr($representation, '\\') !== false)
                    ? '\\' . ltrim($representation, '\\')
                    : $representation;
                $className = (!class_exists($className))
                    ? '\\DrestCommon\\Representation\\' . ltrim($className, '\\')
                    : $className;
                if (!class_exists($className)) {
                    throw RepresentationException::unknownRepresentationClass($representation);
                }
                $representationObjects[] = $representation = new $className();
            }
            if (!$representation instanceof AbstractRepresentation) {
                throw RepresentationException::representationMustBeInstanceOfDrestRepresentation();
            }

            if (($representation = $this->determineRepresentationByHttpMethod($representation, $this->config->getDetectContentOptions())) !== null)
            {
                return $representation;
            }
        }

        // For get requests with "415 for no media match" set on, throw an exception
        if ($this->getRequest()->getHttpMethod() == Request::METHOD_GET && $this->config->get415ForNoMediaMatchSetting()) {
            throw UnableToMatchRepresentationException::noMatch();
        }

        // Return the first instantiated representation instance
        if (isset($representationObjects[0])) {
            return $representationObjects[0];
        }

        return null;
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
        $routesSize = sizeof($matchedRoutes);
        if ($routesSize == 0) {
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
            $representation = $this->getDeterminedRepresentation();
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
     * Get the event manager
     * @return Event\Manager
     */
    public function getEventManager()
    {
        return $this->eventManager;
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
     * Iterates through annotation definitions, any exceptions thrown will bubble up.
     */
    public function checkDefinitions()
    {
        foreach ($this->metadataManager->getAllClassNames() as $class) {
            $this->getClassMetadata($class);
        }
    }
}