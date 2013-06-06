<?php
namespace Drest;


use Drest\Query\ResultSet;

use Doctrine\Common\EventManager,
    Doctrine\Common\Annotations\Annotation,
    Doctrine\Common\Annotations\AnnotationRegistry,
    Doctrine\Common\Annotations\AnnotationReader,

	Doctrine\ORM\EntityManager,
	Doctrine\ORM\Mapping\ClassMetadataInfo as ORMClassMetaDataInfo,

	Drest\Mapping\MetadataFactory,
	Drest\Mapping\RouteMetaData,

	Drest\Error\Handler\AbstractHandler,

	Drest\Representation\AbstractRepresentation,
	Drest\Representation\RepresentationException,
	Drest\Representation\UnableToMatchRepresentationException,

	Drest\Route\MultipleRoutesException,
	Drest\Route\NoMatchException;


class Manager
{

	/**
	 * Doctrine Entity Manager
	 * @var Doctrine\ORM\EntityManager $em
	 */
	protected $em;

	/**
	 * Drest configuration object
	 * @var Drest\Configuration $config
	 */
	protected $config;

	/**
	 * Metadata factory object
	 * @var Drest\Mapping\MetadataFactory $metadataFactory
	 */
	protected $metadataFactory;

	/**
	 * Drest router
	 * @var Drest\Router $router
	 */
	protected $router;

	/**
	 * Drest request object
	 * @var \Drest\Request\Adapter\AdapterInterface $request
	 */
	protected $request;

	/**
	 * Drest response object
	 * @var \Drest\Response\Adapter\AdapterInterface $response
	 */
	protected $response;

	/**
	 * A service object used to handle service actions
	 * @var Drest\Service $service
	 */
	protected $service;

	/**
	 * Error handler object
	 * @var Drest\Error\Handler\AbstractHandler $error_handler
	 */
	protected $error_handler;


    /**
     * Creates an instance of the Drest Manager using the passed configuration object
     * Can also pass in a Doctrine EventManager instance
     *
     * @param \Drest\Configuration $config
     * @param \Doctrine\Common\EventManager $eventManager
     */
    protected function __construct(EntityManager $em, Configuration $config, EventManager $eventManager)
    {
    	$this->em 			= $em;
        $this->config       = $config;
        $this->eventManager = $eventManager;
        $this->service      = new Service($this->em, $this);

        // Router is internal and currently cannot be injected / extended
        $this->router = new Router();

        $this->metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                new AnnotationReader(),
                $config->getPathsToConfigFiles()
            )
        );

        $this->metadataFactory->setCache($config->getMetadataCacheImpl());

        $this->registerRoutes();
    }

    /**
     * Static call to create the Drest Manager instance
     *
     * @param Drest\Configuration $config
     * @param Drest\EventManager $eventManager
     */
	public static function create(EntityManager $em, Configuration $config, EventManager $eventManager = null)
	{
		// Check there is a metadata driver registered (only annotations driver allowed atm

        // Register the annotations classes
        \Drest\Mapping\Driver\AnnotationDriver::registerAnnotations();

		if ($eventManager === null)
		{
			$eventManager = new EventManager();
		}

        return new self($em, $config, $eventManager);
	}

    /**
     * Read any defined route patterns that have been annotated into the router
     */
    protected function registerRoutes()
    {
    	foreach ($this->metadataFactory->getAllClassNames() as $class)
		{
            $classMetaData = $this->getClassMetadata($class);
            foreach ($classMetaData->getRoutesMetaData() as $route)
            {
                $this->router->registerRoute($route);
            }
		}
    }

    /**
     * Dispatch a REST request
     * @param object $request 		- Framework request object
     * @param object $response 		- Framework response object
     * @param string $namedRoute 	- Define the named Route to be dispatch - by passes the internal router
     * @param array $routeParams	- Route parameters to be used when dispatching a namedRoute request
     * @return Drest\Reponse $response return's a Drest response object which can be sent calling toString()
     */
	public function dispatch($request = null, $response = null, $namedRoute = null, array $routeParams = array())
	{
	    $this->setRequest(Request::create($request));
	    $this->setResponse(Response::create($response));
	    try {
	        return $this->execute($namedRoute, $routeParams);
	    } catch (\Exception $e)
	    {
	        // Check debug mode, if set on then rethrow the exception
	        if ($this->config->inDebugMode())
	        {
	            throw $e;
	        }
	        return $this->handleError($e);
	    }
	}

	/**
	 * Handle an error by passing the exception to the registered error handler
	 * @param \Exception $e
	 * @return Drest\Reponse $response
	 */
	private function handleError(\Exception $e)
	{
	    echo $e->getMessage();
	    $eh = $this->getErrorHandler();

        try {
            $representation = $this->getDeterminedRepresentation();
            $errorDocument = $representation->getDefaultErrorResponse();
	        $eh->error($e, 500, $errorDocument);
        } catch (UnableToMatchRepresentationException $e)
        {
            $errorDocument = new \Drest\Error\Response\Text();
            $eh->error($e, 500, $errorDocument);
        }

        $this->response->setStatusCode($eh->getReponseCode());
        $this->response->setHttpHeader('Content-Type', $errorDocument::getContentType());
        $this->response->setBody($errorDocument->render());

	    return $this->response;
	}


	/**
	 * Get a route based on Entity::route_name. eg Entities\User::get_users
	 * Syntax checking is performed
	 * @param string $name
	 * @param array $params
	 * @throws DrestException on invalid syntax or unmatched named route
	 * @return Drest\Mapping\RouteMetaData $route
	 */
	protected function getNamedRoute($name, array $params = array())
	{
	    if (substr_count($name, '::') !== 1)
	    {
	        throw DrestException::invalidNamedRouteSyntax();
	    }
	    $parts = explode('::', $name);

	    // Allow exception to bubble up
	    $classMetaData = $this->getClassMetadata($parts[0]);
	    if (($route = $classMetaData->getRoutesMetaData($parts[1])) === false)
	    {
            throw DrestException::unableToFindRouteByName($parts[1], $classMetaData->getClassName());
	    }

	    $route->setRouteParams($params);
	    return $route;
	}

	/**
	 * Execute a dispatched request
     * @param string $namedRoute 		- Define the named Route to be dispatched - bypasses the internal router lookup
     * @param array $routeParams		- Route parameters to be used for dispatching a namedRoute request
     * @return Drest\Reponse $response 	- Returns a Drest response object which can be sent calling toString()
	 */
	protected function execute($namedRoute = null, array $routeParams = array())
	{
		// Perform a match based on the current URL / Header / Params - remember to include HTTP VERB checking when performing a matched() call
		// @todo: tidy this up
		try {
            $route = (!is_null($namedRoute)) ? $this->getNamedRoute($namedRoute, $routeParams) : $this->getMatchedRoute(true);
		} catch (\Exception $e)
		{
		    if ($e instanceof NoMatchException && ($this->doCGOptionsCheck() || $this->doOptionsCheck()))
		    {
                return $this->getResponse();
		    }
            throw $e;
		}

		// Get the reperesentation to be used
		$representation = $this->getDeterminedRepresentation($route);
		switch ($this->getRequest()->getHttpMethod())
		{
		    case Request::METHOD_GET:
		        $route = $this->handlePullExposureConfiguration($route);
		        break;
		    case Request::METHOD_POST:
            case Request::METHOD_PUT:
            case Request::METHOD_PATCH:
                $representation = $this->handlePushExposureConfiguration($route, $representation);
                break;
		}

        // Set parameters matched on the route to the request object
        $this->request->setRouteParam($route->getRouteParams());


        // Set the matched service object and the error handler into the service class
        $this->service->setMatchedRoute($route);
        $this->service->setRepresentation($representation);
        $this->service->setErrorHandler($this->getErrorHandler());

        // Set up the service for a new request
        if ($this->service->setupRequest())
        {
            $this->service->runCallMethod();
        }

        return $this->getResponse();
	}

    /**
     * Handle a pull requests' exposure configuration (GET)
     * @param Drest\Mapping\RouteMetaData $route
     * @return Drest\Mapping\RouteMetaData $route
     */
	protected function handlePullExposureConfiguration(RouteMetaData $route)
	{
        $route->setExpose(
            Query\ExposeFields::create($route)
            ->configureExposeDepth($this->em, $this->config->getExposureDepth(), $this->config->getExposureRelationsFetchType())
            ->configurePullRequest($this->config->getExposeRequestOptions(), $this->request)
            ->toArray()
        );
        return $route;
	}

	/**
	 * Handle a push requests' exposure configuration (POST/PUT/PATCH)
	 * @param Drest\Mapping\RouteMetaData $route - the matched route
	 * @param Drest\Representation\AbstractRepresentation $representation - the representation class to be used
	 * @return Drest\Representation\AbstractRepresentation $representation
	 */
	protected function handlePushExposureConfiguration(RouteMetaData $route, AbstractRepresentation $representation)
	{
        $representation = $representation::createFromString($this->request->getBody());
        // Write the filtered expose data
        $representation->write(
            Query\ExposeFields::create($route)
                ->configureExposeDepth($this->em, $this->config->getExposureDepth(), $this->config->getExposureRelationsFetchType())
                ->configurePushRequest($representation->toArray())
        );
        return $representation;
	}


	/**
	 * Check if the client has requested the CG classes with an OPTIONS call
	 */
	protected function doCGOptionsCheck()
	{
	    $genClasses = $this->request->getHeaders(ClassGenerator::HEADER_PARAM);
        if ($this->request->getHttpMethod() != Request::METHOD_OPTIONS || empty($genClasses))
	    {
	        return false;
	    }

	    $classGenerator = new \Drest\ClassGenerator($this->em);

	    $classMetaDatas = array();
	    if (!empty($genClasses))
	    {
            foreach ($this->metadataFactory->getAllClassNames() as $className)
	        {
	            $metaData = $this->getClassMetadata($className);
                foreach ($metaData->getRoutesMetaData() as $route)
                {
    	            $route = $route->setExpose(
                        Query\ExposeFields::create($route)
                        ->configureExposeDepth($this->em, $this->config->getExposureDepth(), $this->config->getExposureRelationsFetchType())
                        ->toArray()
                    );
                }
                $classMetaDatas[] = $metaData;
	        }
	    }

	    $classGenerator->create($classMetaDatas);

	    $this->response->setBody($classGenerator->serialize());
	    return true;
	}

	/**
	 * No match on route has occured. Check the HTTP verb used for an options response
	 * Returns true if it is, and option information was successfully written to the reponse object
	 * @return boolean $success
	 */
	protected function doOptionsCheck()
	{
	    if ($this->request->getHttpMethod() != Request::METHOD_OPTIONS)
	    {
	        return false;
	    }

	    // Do a match on all routes - dont include a verb check
	    $verbs = array();
        foreach ($this->getMatchedRoutes(false) as $route)
        {
            $allowedOptions = $route->isAllowedOptionRequest();
            if (false === (($allowedOptions === -1) ? $this->config->getAllowOptionsRequest() : (bool) $allowedOptions))
            {
                continue;
            }
            $verbs = array_merge($verbs, $route->getVerbs());
        }

        if (empty($verbs))
        {
            return false;
        }

        $this->getResponse()->setHttpHeader('Allow', implode(', ', $verbs));
        return true;
	}

	/**
	 * Detect an instance of a representation class using a matched route, or default representation classes
	 * @param Mapping\RouteMetaData $route
	 * @return Drest\Representation\AbstractRepresentation $representation
	 * @throw RepresentationException if unable to instantiate a representation object from config settings
	 */
	protected function getDeterminedRepresentation(Mapping\RouteMetaData $route = null)
	{
	    $representations = (!is_null($route)) ? $route->getClassMetaData()->getRepresentations() : $this->config->getDefaultRepresentations();
        if (empty($representations))
	    {
	        throw RepresentationException::noRepresentationsSetForRoute($route);
        }

        $representationObjects = array();
	    foreach ($representations as $representation)
	    {
	        if (!is_object($representation))
	        {
	            // Check if the class is namespaced, if so instantiate from root
	            $className = (strstr($representation, '\\') !== false) ? '\\' . ltrim($representation, '\\') : $representation;
                $className = (!class_exists($className)) ? '\\Drest\\Representation\\' . ltrim($className, '\\') : $className;
	            if (!class_exists($className))
	            {
	                throw RepresentationException::unknownRepresentationClass($representation);
	            }
	            $representationObjects[] = $representation = new $className();
	        }
	        if (!$representation instanceof AbstractRepresentation)
	        {
	            throw RepresentationException::representationMustBeInstanceOfDrestRepresentation();
	        }

	        switch ($this->request->getHttpMethod())
	        {
	            // Match on content option
	            case Request::METHOD_GET:
	        	    // This representation matches the required media type requested by the client
                    if ($representation->isExpectedContent($this->config->getDetectContentOptions(), $this->request))
                    {
                        return $representation;
                    }
	                break;
                // Match on content-type
	            case Request::METHOD_POST:
	            case Request::METHOD_PUT:
                case Request::METHOD_PATCH:
	                if ($representation->getContentType() === $this->request->getHeaders('Content-Type'))
	                {
	                    return $representation;
	                }
	                break;
	        }
	    }

	    // If we don't match the requested media type, throw a not supported error
	    if (!$this->config->get415ForNoMediaMatchSetting())
	    {
    	    // Return the first instantiated representation instance
    	    if (isset($representationObjects[0]))
    	    {
    	        return $representationObjects[0];
    	    }
	    }

		// We have no representation instances from either annotations or config object
		throw \Drest\Representation\UnableToMatchRepresentationException::noMatch();
	}


	/**
	 * Runs through all the registered routes and returns a single match
	 * @param boolean $matchVerb - Whether you want to match the route using the request HTTP verb
	 * @throws NoMatchException if no routes are found
	 * @throws MultipleRoutesException If there are multiple matches
	 * @return Drest\Mapping\RouteMetaData $route
	 */
	protected function getMatchedRoute($matchVerb = true)
	{
	    // Inject any route base Paths that have been registered
	    if ($this->config->hasRouteBasePaths())
	    {
	        $this->router->setRouteBasePaths($this->config->getRouteBasePaths());
	    }

        $matchedRoutes = $this->router->getMatchedRoutes($this->getRequest(), (bool) $matchVerb);
        $routesSize = sizeof($matchedRoutes);
        if ($routesSize == 0)
        {
            throw NoMatchException::noMatchedRoutes();
        } elseif (sizeof($matchedRoutes) > 1)
		{
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
	    return $this->router->getMatchedRoutes($this->getRequest(), (bool) $matchVerb);
	}

	/**
	 * Get the request object
	 * @param $fwRequest - constructed using a fw adapted object
	 * @return Drest\Request $request
	 */
	public function getRequest($fwRequest = null)
	{
		if (!$this->request instanceof Request)
		{
			$this->request = Request::create($fwRequest);
		}
		return $this->request;
	}

	/**
	 * Set the request object
	 * @param Drest\Request $request
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}


	/**
	 * Get the response object
	 * @param $fwResponse - constructed using a fw adapted object
	 * @return Drest\Response $response
	 */
	public function getResponse($fwResponse = null)
	{
        if (!$this->response instanceof Response)
		{
			$this->response = Response::create($fwResponse);
		}
	    return $this->response;
	}

	/**
	 * Set the response object
	 * @param Drest\Response $response
	 */
	public function setResponse(Response $response)
	{
	    $this->response = $response;
	}

	/**
	 * Get the error handler object, if none has been injected use default from config
	 * @return Drest\Error\Handler\AbstractHandler $error_handler
	 */
	public function getErrorHandler()
	{
	    if (!$this->error_handler instanceof AbstractHandler)
	    {
	        // Force creation of an instance of the default error handler
	        $className = $this->config->getDefaultErrorHandlerClass();
	        $this->error_handler = new $className();
	    }
        return $this->error_handler;
	}

	/**
	 * Set the error handler to use
	 * @param Drest\Error\Handler\AbstractHandler $error_handler
	 */
	public function setErrorHandler(AbstractHandler $error_handler)
	{
        $this->error_handler = $error_handler;
	}

    /**
     * Get metadata for an entity class
     * @param Drest\Mapping\ClassMetaData $classMetaData
     */
    public function getClassMetadata($className)
    {
        return $this->metadataFactory->getMetadataForClass($className);
    }
}