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

	Drest\Request,
	Drest\Query,
	Drest\Service\AbstractService,
	Drest\Writer\AbstractWriter,
	Drest\Writer\WriterException,
	Drest\Writer\UnableToMatchWriterException,
	Drest\DrestException,
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
	 * A cache for initialised service classes
	 * @var array contains array of service classes of instance Drest\Service\AbstractService
	 */
	protected $services;

	/**
	 * The matched service object from a dispatch() request
	 * @var Drest\Service\AbstractService $service
	 */
	protected $service;


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
            $classMetaData = $this->metadataFactory->getMetadataForClass($class);
            foreach ($classMetaData->getRoutesMetaData() as $route)
            {
                $this->router->registerRoute($route);
            }
		}
    }

    /**
     * Dispatch a REST request
     * @param object $request - Framework request object
     * @param object $response - Framework response object
     * @return Drest\Reponse $response return's a Drest response object which can be sent calling toString()
     */
	public function dispatch($request = null, $response = null)
	{
	    $this->setRequest(Request::create($request));
	    $this->setResponse(Response::create($response));
	    try {
	        return $this->execute();
	    } catch (\Exception $e)
	    {
	        // Check debug mode, if set on them rethrow the exception
	        if ($this->config->inDebugMode())
	        {
	            throw $e;
	        }
            return $this->systemError($e);
	    }
	}

	/**
	 * Called when an error occurs on dispatch and we're not in debug mode
	 * Sets a general error response document
	 * @todo: abstract this, it shouldn't be here
	 * @param Exception $exception
	 */
	private function systemError(\Exception $exception)
	{
        $response = $this->getResponse();
        // @todo: standardise the error response, current defaults to the framework impl - return error in format (writer) requested
        switch (get_class($exception))
        {
            case 'Drest\Query\InvalidExposeFieldsException':
                $response->setStatusCode(Response::STATUS_CODE_400);
                $error_message = $exception->getMessage();
                break;
            case 'Drest\Route\NoMatchException':
                $response->setStatusCode(Response::STATUS_CODE_404);
                $error_message = $exception->getMessage();
                break;
            case 'Drest\Writer\UnableToMatchWriterException';
                $response->setStatusCode(Response::STATUS_CODE_415);
                $error_message = 'Requested media type is not supported';
                break;
            default:
                // Drest\Route\MultipleRoutesException
                $response->setStatusCode(Response::STATUS_CODE_500);
                $error_message = 'An unknown error occured';
                break;
        }

        $resultSet = ResultSet::create(array($error_message), 'errors');

        // Use a predetermined writer to generate the error output, else default to text
        if ($this->service instanceof AbstractService &&
            $this->service->getWriter() instanceof AbstractWriter)
        {
            $this->service->renderDeterminedWriter($resultSet);
        } else
        {
            try {
                $writer = $this->getDeterminedWriter();
            } catch (\Exception $e) {
                $writer = new \Drest\Writer\Text();
            }
            $response->setBody($writer->write($resultSet));
            $response->setHttpHeader('Content-Type', $writer->getContentType());
        }

        return $response;
	}

	/**
	 * Execute a dispatched request
     * @return Drest\Reponse $response return's a Drest response object which can be sent calling toString()
	 */
	protected function execute()
	{
		// Perform a match based on the current URL / Header / Params - remember to include HTTP VERB checking when performing a matched() call
		// @todo: tidy this up
		try {
            $route = $this->getMatchedRoute(true);
		} catch (\Exception $e)
		{
		    if ($e instanceof NoMatchException && $this->doOptionsCheck())
		    {
                return $this->getResponse();
		    }
            throw $e;
		}

        // Setup exposure fields on GET requests
        if ($this->getRequest()->getHttpMethod() == Request::METHOD_GET)
        {
            $route->setExpose(
                Query\ExposeFields::create($route)
                ->configureExposeDepth($this->em, $this->config->getExposureDepth(), $this->config->getExposureRelationsFetchType())
                ->configureExposureRequest($this->config->getExposeRequestOptions(), $this->request)
                ->toArray()
            );
        }

        // Set paramaters matched on the route to the request object
        $this->request->setRouteParam($route->getRouteParams());

        // Get the service class
        $this->service = $this->getService($route);

        // Set the matched service object into the service class
        $this->service->setMatchedRoute($route);


        try
        {
            // This method could either determine a default writer, or a throw 415 unsupported
            $this->service->setWriter($this->getDeterminedWriter($route));
        } catch (UnableToMatchWriterException $e)
        {
            return $this->systemError($e);
        }

        // Set up the service for a new request
        if ($this->service->setupRequest())
        {
            $this->service->runCallMethod();
        }

        return $this->getResponse();
	}


	/**
	 * No match on route has occured. Check the HTTP verb used for an options response
	 * Returns true if it is, and option information was successfully written to the reponse object
	 * @return boolean $success
	 */
	protected function doOptionsCheck()
	{
	    // Is this an OPTIONS request
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
	 * Detect an instance of a writer class using a matched route, or default writer classes
	 * @param Mapping\RouteMetaData $route
	 * @return Drest\Writer\AbstractWriter $writer
	 * @throw WriterException of unable to instantiate a write from config settings
	 */
	protected function getDeterminedWriter(Mapping\RouteMetaData $route = null)
	{
	    $writers = (!is_null($route)) ? $route->getClassMetaData()->getWriters() : $this->config->getDefaultWriters();
        if (empty($writers))
	    {
	        throw WriterException::noWritersSetForRoute($route);
        }

        $writerObjects = array();
	    foreach ($writers as $writer)
	    {
	        if (!is_object($writer))
	        {
	            // Check if the class is namespaced, if so instantiate from root
	            $className = (strstr($writer, '\\') !== false) ? '\\' . ltrim($writer, '\\') : $writer;
                $className = (!class_exists($className)) ? '\\Drest\\Writer\\' . ltrim($className, '\\') : $className;
	            if (!class_exists($className))
	            {
	                throw WriterException::unknownWriterClass($writer);
	            }
	            $writerObjects[] = $writer = new $className();
	        }
	        if (!$writer instanceof Writer\AbstractWriter)
	        {
	            throw WriterException::writerMustBeInstanceOfDrestWriter();
	        }

	        // This writer matches the required media type requested by the client
            if ($writer->isExpectedContent($this->config->getDetectContentOptions(), $this->request))
            {
                return $writer;
            }
	    }

	    // If we don't match the requested media type, throw a not supported error
	    if (!$this->config->get415ForNoWriterMatchSetting())
	    {
    	    // Return the first instantiated writer instance
    	    if (isset($writerObjects[0]))
    	    {
    	        return $writerObjects[0];
    	    }
	    }

		// We have no writer instances from either annotations or config object
        throw \Drest\Writer\UnableToMatchWriterException::noMatch();
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
     * Get the service class for the matched route - provides default service class if none present
     * @param Drest\Mapping\RouteMetaData $route - the matched route
     * @return Drest\Service\AbstractService $service - the service class
     * @throws DrestException if defined service class is not an instance of Drest\Service\AbstractService
     */
	public function getService(RouteMetaData $route)
	{
	    $serviceClassName = ltrim($route->getServiceCallClass(), '\\');
	    $serviceClassName = (!empty($serviceClassName)) ? (strpos($serviceClassName, '\\') === 0) ? $serviceClassName : '\\' . $serviceClassName
	                                                     : $this->config->getDefaultServiceClass();

        // Return the already cached instance of the service class
		if (!empty($serviceClassName) && isset($this->services[$serviceClassName]))
	    {
	        return $this->services[$serviceClassName];
	    }

	    $service = new $serviceClassName($this->em, $this);
	    if (!$service instanceof Service\AbstractService)
	    {
	        throw DrestException::serviceClassNotAnInstanceOfDrestService(get_class($service));
	    }

	    $this->services[$serviceClassName] = $service;
	    return $service;
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