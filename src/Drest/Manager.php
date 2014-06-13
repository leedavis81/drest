<?php
/**
 * This file is part of the Drest package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Lee Davis
 * @copyright Copyright (c) Lee Davis <leedavis81@hotmail.com>
 * @link https://github.com/leedavis81/drest/blob/master/LICENSE.txt
 * @license http://opensource.org/licenses/MIT The MIT X License (MIT)
 */

namespace Drest;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Drest\Mapping\MetadataFactory;
use Drest\Mapping\RouteMetaData;
use Drest\Event;
use DrestCommon\Error\Handler\AbstractHandler;
use DrestCommon\Error\Response\Text as ErrorResponseText;
use DrestCommon\Request\Request;
use DrestCommon\Response\Response;
use DrestCommon\Representation\AbstractRepresentation;
use DrestCommon\Representation\RepresentationException;
use DrestCommon\Representation\UnableToMatchRepresentationException;
use Drest\Route\MultipleRoutesException;
use Drest\Route\NoMatchException;

class Manager
{

    /**
     * Doctrine Entity Manager
     * @var EntityManager $em
     */
    protected $em;

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
     * Metadata factory object
     * @var \Drest\Mapping\MetadataFactory $metadataFactory
     */
    protected $metadataFactory;

    /**
     * Drest router
     * @var \Drest\Router $router
     */
    protected $router;

    /**
     * Drest request object
     * @var \DrestCommon\Request\Request $request
     */
    protected $request;

    /**
     * Drest response object
     * @var Response $response
     */
    protected $response;

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
     * @param EntityManager $em
     * @param Configuration $config
     * @param Event\Manager $eventManager
     */
    private function __construct(EntityManager $em, Configuration $config, Event\Manager $eventManager)
    {
        $this->em = $em;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->service = new Service($this->em, $this);

        // Router is internal and currently cannot be injected / extended
        $this->router = new Router();

        $this->metadataFactory = new MetadataFactory(
            Mapping\Driver\AnnotationDriver::create(
                new AnnotationReader(),
                $config->getPathsToConfigFiles()
            )
        );

        if ($cache = $config->getMetadataCacheImpl())
        {
            $this->metadataFactory->setCache($cache);
        }
    }

    /**
     * Static call to create the Drest Manager instance
     *
     * @param EntityManager $em
     * @param Configuration $config
     * @param Event\Manager $eventManager
     * @return Manager $manager
     */
    public static function create(EntityManager $em, Configuration $config, Event\Manager $eventManager = null)
    {
        // Register the annotations classes
        Mapping\Driver\AnnotationDriver::registerAnnotations();

        if ($eventManager === null) {
            $eventManager = new Event\Manager();
        }

        return new self($em, $config, $eventManager);
    }

    /**
     * Dispatch a REST request
     * @param object $request            - Framework request object
     * @param object $response           - Framework response object
     * @param string $namedRoute         - Define the named Route to be dispatch - by passes the internal router
     * @param array $routeParams         - Route parameters to be used when dispatching a namedRoute request
     * @return Response $response - returns a Drest response object which can be sent calling toString()
     * @throws \Exception                - Upon failure
     */
    public function dispatch($request = null, $response = null, $namedRoute = null, array $routeParams = array())
    {
        $this->setRequest(Request::create($request, $this->config->getRegisteredRequestAdapterClasses()));
        $this->setResponse(Response::create($response, $this->config->getRegisteredResponseAdapterClasses()));

        // Register routes for lookup
        $this->registerRoutes();

        // trigger preDispatch event
        $this->getEventManager()->dispatchEvent(
            Event\Events::PRE_DISPATCH,
            new Event\PreDispatchArgs($this->service)
        );

        $rethrowException = false;
        try {
            $this->execute($namedRoute, $routeParams);
        } catch (\Exception $e) {

            if ($this->config->inDebugMode())
            {
                $rethrowException = $e;
            } else
            {
                $this->handleError($e);
            }
        }

        // trigger a postDispatch event
        $this->getEventManager()->dispatchEvent(
            Event\Events::POST_DISPATCH,
            new Event\PostDispatchArgs($this->service)
        );

        if ($rethrowException)
        {
            throw $rethrowException;
        }

        return $this->response;
    }

    /**
     * Execute a dispatched request
     * @param string $namedRoute        - Define the named Route to be dispatched - bypasses the internal router lookup
     * @param array $routeParams        - Route parameters to be used for dispatching a namedRoute request
     * @throws Route\NoMatchException|\Exception
     */
    protected function execute($namedRoute = null, array $routeParams = array())
    {
        if (($route = $this->determineRoute($namedRoute, $routeParams)) instanceof RouteMetaData)
        {
            // Get the representation to be used - always successful or it throws an exception
            $representation = $this->getDeterminedRepresentation($route);

            // Configure push / pull exposure fields
            switch ($this->request->getHttpMethod()) {
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
     * @param null $namedRoute
     * @param array $routeParams
     * @throws Route\NoMatchException|\Exception
     * @return RouteMetaData|bool $route - if false no route could be matched
     * (ideally the response should be returned in this instance - fail fast)
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
                ($this->doCGOptionsCheck() || $this->doOptionsCheck())) {
                return false;
            }
            throw $e;
        }
        // dispatch postRoutingAction event
        $this->getEventManager()->dispatchEvent(Event\Events::POST_ROUTING, new Event\PostRoutingArgs($this->service));

        // Set parameters matched on the route to the request object
        $this->request->setRouteParam($route->getRouteParams());

        return $route;
    }

    /**
     * Get a route based on Entity::route_name. eg Entities\User::get_users
     * Syntax checking is performed
     * @param string $name
     * @param array $params
     * @throws DrestException on invalid syntax or unmatched named route
     * @return RouteMetaData $route
     */
    protected function getNamedRoute($name, array $params = array())
    {
        if (substr_count($name, '::') !== 1) {
            throw DrestException::invalidNamedRouteSyntax();
        }
        $parts = explode('::', $name);

        // Allow exception to bubble up
        $classMetaData = $this->getClassMetadata($parts[0]);
        if (($route = $classMetaData->getRoutesMetaData($parts[1])) === false) {
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
                    $this->em,
                    $this->config->getExposureDepth(),
                    $this->config->getExposureRelationsFetchType())
                ->configurePullRequest($this->config->getExposeRequestOptions(), $this->request)
                ->toArray()
        );
    }

    /**
     * Handle a push requests' exposure configuration (POST/PUT/PATCH)
     * @param RouteMetaData $route - the matched route
     * @param AbstractRepresentation $representation - the representation class to be used
     * @return AbstractRepresentation $representation
     */
    protected function handlePushExposureConfiguration(RouteMetaData $route, AbstractRepresentation $representation)
    {
        $representation = $representation::createFromString($this->request->getBody());
        // Write the filtered expose data
        $representation->write(
            Query\ExposeFields::create($route)
                ->configureExposeDepth(
                    $this->em,
                    $this->config->getExposureDepth(),
                    $this->config->getExposureRelationsFetchType())
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
        $genClasses = $this->request->getHeaders(ClassGenerator::HEADER_PARAM);
        if ($this->request->getHttpMethod() != Request::METHOD_OPTIONS || empty($genClasses)) {
            return false;
        }

        $classGenerator = new ClassGenerator($this->em);

        $classMetadatas = array();
        if (!empty($genClasses)) {
            foreach ($this->metadataFactory->getAllClassNames() as $className) {
                $metaData = $this->getClassMetadata($className);
                foreach ($metaData->getRoutesMetaData() as $route) {
                    /* @var RouteMetaData $route */
                    $route->setExpose(
                        Query\ExposeFields::create($route)
                            ->configureExposeDepth(
                                $this->em,
                                $this->config->getExposureDepth(),
                                $this->config->getExposureRelationsFetchType())
                            ->toArray()
                    );
                }
                $classMetadatas[] = $metaData;
            }
        }

        $classGenerator->create($classMetadatas);

        $this->response->setBody($classGenerator->serialize());
        return true;
    }

    /**
     * No match on route has occurred. Check the HTTP verb used for an options response
     * Returns true if it is, and option information was successfully written to the response object
     * @return boolean $success
     */
    protected function doOptionsCheck()
    {
        if ($this->request->getHttpMethod() != Request::METHOD_OPTIONS) {
            return false;
        }

        // Do a match on all routes - don't include a verb check
        $verbs = array();
        foreach ($this->getMatchedRoutes(false) as $route) {
            /* @var RouteMetaData $route */
            $allowedOptions = $route->isAllowedOptionRequest();
            if (false === (($allowedOptions === -1)
                    ? $this->config->getAllowOptionsRequest()
                    : (bool)$allowedOptions)) {
                continue;
            }
            $verbs = array_merge($verbs, $route->getVerbs());
        }

        if (empty($verbs)) {
            return false;
        }

        $this->response->setHttpHeader('Allow', implode(', ', $verbs));
        return true;
    }

    /**
     * Detect an instance of a representation class using a matched route, or default representation classes
     * @param RouteMetaData $route
     * @param Mapping\RouteMetaData $route
     * @throws UnableToMatchRepresentationException
     * @throws RepresentationException - if unable to instantiate a representation object from config settings
     * @return AbstractRepresentation $representation
     */
    protected function getDeterminedRepresentation(Mapping\RouteMetaData &$route = null)
    {
        $representations = (!is_null($route))
            ? $route->getClassMetaData()->getRepresentations()
            : $this->config->getDefaultRepresentations();
        if (empty($representations)) {
            throw RepresentationException::noRepresentationsSetForRoute(
                $route->getName(),
                $route->getClassMetaData()->getClassName());
        }

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

            switch ($this->request->getHttpMethod()) {
                // Match on content option
                case Request::METHOD_GET:
                    // This representation matches the required media type requested by the client
                    if ($representation->isExpectedContent($this->config->getDetectContentOptions(), $this->request)) {
                        return $representation;
                    }
                    break;
                // Match on content-type
                case Request::METHOD_POST:
                case Request::METHOD_PUT:
                case Request::METHOD_PATCH:
                    if ($representation->getContentType() === $this->request->getHeaders('Content-Type')) {
                        return $representation;
                    }
                    break;
            }
        }

        // For get requests with "415 for no media match" set on, throw an exception
        if ($this->request->getHttpMethod() == Request::METHOD_GET && $this->config->get415ForNoMediaMatchSetting()) {
            throw UnableToMatchRepresentationException::noMatch();
        }

        // Return the first instantiated representation instance
        if (isset($representationObjects[0])) {
            return $representationObjects[0];
        }

        // We have no representation instances from either annotations or config object
        throw UnableToMatchRepresentationException::noMatch();
    }

    /**
     * Read any defined route patterns that have been annotated into the router
     */
    protected function registerRoutes()
    {
        foreach ($this->metadataFactory->getAllClassNames() as $class) {
            $classMetaData = $this->getClassMetadata($class);
            foreach ($classMetaData->getRoutesMetaData() as $route) {
                $this->router->registerRoute($route);
            }
        }
    }

    /**
     * Runs through all the registered routes and returns a single match
     * @param boolean $matchVerb - Whether you want to match the route using the request HTTP verb
     * @throws NoMatchException if no routes are found
     * @throws MultipleRoutesException If there are multiple matches
     * @return RouteMetaData $route
     */
    protected function getMatchedRoute($matchVerb = true)
    {
        // Inject any route base Paths that have been registered
        if ($this->config->hasRouteBasePaths()) {
            $this->router->setRouteBasePaths($this->config->getRouteBasePaths());
        }

        $matchedRoutes = $this->router->getMatchedRoutes($this->request, (bool)$matchVerb);
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
     * @param boolean $matchVerb - Whether you want to match the route using the request HTTP verb
     * @return array of Drest\Mapping\RouteMetaData object
     */
    protected function getMatchedRoutes($matchVerb = true)
    {
        return $this->router->getMatchedRoutes($this->request, (bool)$matchVerb);
    }

    /**
     * Handle an error by passing the exception to the registered error handler
     * @param \Exception $e
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

        $this->response->setStatusCode($eh->getResponseCode());
        $this->response->setHttpHeader('Content-Type', $errorDocument::getContentType());
        $this->response->setBody($errorDocument->render());
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
     * Get the request object
     * @param $fwRequest - constructed using a fw adapted object
     * @return Request $request
     */
    public function getRequest($fwRequest = null)
    {
        if (!$this->request instanceof Request) {
            $this->request = Request::create($fwRequest, $this->config->getRegisteredRequestAdapterClasses());
        }
        return $this->request;
    }

    /**
     * Set the request object
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the response object
     * @param $fwResponse - constructed using a fw adapted object
     * @return Response $response
     */
    public function getResponse($fwResponse = null)
    {
        if (!$this->response instanceof Response) {
            $this->response = Response::create($fwResponse, $this->config->getRegisteredResponseAdapterClasses());
        }
        return $this->response;
    }

    /**
     * Set the response object
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
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
     * @param string $className
     * @return Mapping\ClassMetaData
     */
    public function getClassMetadata($className)
    {
        return $this->metadataFactory->getMetadataForClass($className);
    }

    /**
     * Iterates through annotation definitions, any exceptions thrown will bubble up.
     */
    public function checkDefinitions()
    {
        foreach ($this->metadataFactory->getAllClassNames() as $class)
        {
            $this->getClassMetadata($class);
        }
    }
}