<?php
namespace Drest;


use Doctrine\Common\EventManager,
    Doctrine\Common\Annotations\Annotation,
    Doctrine\Common\Annotations\AnnotationRegistry,
    Doctrine\Common\Annotations\AnnotationReader,
	Doctrine\ORM\EntityManager,

	Drest\Mapping\MetadataFactory,

	Drest\Request,
	Drest\Repository,
	Drest\DrestException;


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
	 * @var Metadata\MetadataFactory $metadataFactory
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
     * Read any defined route patterns that have been annotated into the router
     */
    protected function registerRoutes()
    {
    	foreach ($this->metadataFactory->getAllClassNames() as $class)
		{
            $classMetaData = $this->metadataFactory->getMetadataForClass($class);
            foreach ($classMetaData->getServicesMetaData() as $service)
            {
                $this->router->registerRoute($service);
            }
		}
    }

    /**
     * Static call to create the Drest Manager instance
     *
     * @param unknown_type $config
     * @param unknown_type $eventManager
     */
	public static function create(EntityManager $em, Configuration $config, EventManager $eventManager = null)
	{
		// Run some configuration checks
//		if ( ! $config->getMetadataDriverImpl()) {
//            throw DrestException::missingMappingDriverImpl();
//        }

		if ($eventManager === null)
		{
			$eventManager = new EventManager();
		}

        return new self($em, $config, $eventManager);
	}



    /**
     *
     * Dispatch a REST request
     * @param object $request - Framework request object
     * @param object $response - Framework response object
     * @return Drest\Reponse $response return's a Drest response object which can be sent calling toString()
     */
	public function dispatch($request = null, $response = null)
	{
	    try {
	        return $this->execute($request, $response);
	    } catch (\Exception $e)
	    {
	        // Check debug mode, if set on them rethrow the exception
	        if ($this->config->inDebugMode())
	        {
	            throw $e;
	        }

            return $this->systemError();
	    }
	}

	/**
	 * Called when an error occurs when routing the request outside of debug mode
	 * Sets a general error response document
	 */
	private function systemError()
	{
        $response = $this->getResponse();
        // A Drest exception has occured, send an unknown error response
        $response->setStatusCode(Response::STATUS_CODE_500);

        // @todo: possibly standardise the error response, current defaults to the framework impl

        return $response;
	}

	/**
	 *
	 * Execute a dispatched request
     * @param object $request - Framework request object
     * @param object $response - Framework response object
     * @return Drest\Reponse $response return's a Drest response object which can be sent calling toString()
	 */
	protected function execute($request = null, $response = null)
	{
		// Perform a match based on the current URL / Header / Params - remember to include HTTP VERB checking when performing a matched() call
        $service = $this->getMatchedRoute();

        $repository = $this->getRepository($service->getClassMetaData());

        // Set paramaters matched on the route to the request object
        $this->request->setRouteParam($service->getRouteParams());

        // Set the matched service object into the repository class
        $repository->setMatchedService($service);

    	// Inject the request / response object into the extended repository
    	$repository->setRequest($this->getRequest($request));
    	$repository->setResponse($this->getResponse($response));

        // Fetch an instance of Drest\Repository
        $repositoryMethod = $service->getRepositoryMethod();
        if (empty($repositoryMethod))
        {
            // If nothing was defined, execute the default request method
            $data = $repository->executeDefaultMethod($service);
        } elseif (!method_exists($repository, $repositoryMethod))
        {
            throw DrestException::unknownRepositoryMethod(get_class($repository), $repositoryMethod);
        } else
        {
            $data = $repository->$repositoryMethod();
        }

        // Pass the results to a writer
        if ($this->response->getStatusCode() == 200 && isset($data))
        {
            $this->execWriter($service, $data);
        }

        return $this->getResponse();
	}

    /**
     * Detect the writer to be applied, pass in the data and write the content to the response object
     */
	protected function execWriter(Mapping\ServiceMetaData $service, array $data = array())
	{
	    $writers = $service->getClassMetaData()->getWriters();
	    if (empty($writers))
	    {
	        throw DrestException::noWritersSetForService($service);
	    }

        $writerFound = false;
	    foreach ($writers as $writer)
	    {
            if ($this->detectContentWriter($writer))
            {
                $this->response->setBody($writer->write($data));
                $writerFound = true;
                break;
            }
	    }

	    if (!$writerFound)
	    {
	        throw DrestException::unableToMatchAWriter();
	    }
	}

	/**
	 * If content type can be detected through config mechanism, then this returns true
	 * @param Writer\AbstractWriter $writer
	 * @return boolean upon success of matching a content writer
	 */
	protected function detectContentWriter(Writer\AbstractWriter $writer)
	{
	    foreach ($this->config->getDetectContentOptions() as $detectContentOption)
	    {
	        switch ($detectContentOption)
	        {
                case Configuration::DETECT_CONTENT_ACCEPT_HEADER:
                    $acceptHeader = explode(';', $this->request->getHeaders('Accept'));
                    // See if the Accept header matches for this writer
                    if (in_array($this->request->getHeaders('Accept'), $writer->getMatchableAcceptHeaders()))
                    {
                        return true;
                    }
                break;
	            case Configuration::DETECT_CONTENT_EXTENSION:
	                // See if an extension has been supplied
	                $ext = $this->request->getExtension();
                    if (!empty($ext) && in_array($this->request->getExtension(), $writer->getMatchableExtensions()))
                    {
                        return true;
                    }
                break;
                case Configuration::DETECT_CONTENT_PARAM:
                    // Inspect the request object for a "format" parameter
                    if (in_array($this->request->getQuery('format'), $writer->getMatchableFormatParams()))
                    {
                        return true;
                    }
                break;
	        }
	    }

	    return false;
	}

	/**
	 * Runs through all the registered routes and returns a single match
	 * @throws DrestException if no routes are found, or there are multiple matches
	 * @return Drest\Mapping\ServiceMetaData $service
	 */
	protected function getMatchedRoute()
	{
        $matchedRoutes = $this->router->getMatchedRoutes($this->getRequest());
        $routesSize = sizeof($matchedRoutes);
        if ($routesSize == 0)
        {
            throw DrestException::noMatchedRoutes();
        } elseif (sizeof($matchedRoutes) > 1)
		{
            throw DrestException::multipleRoutesFound($matchedRoutes);
		}
		return $matchedRoutes[0];
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
	 * @todo: resolve this
	 * Get the router object
	 * @return Drest\Router $router - uses the adapted instance if set, otherwise creates the default router instance
	 */
	public function getRouter()
	{
		if (!$this->router instanceof \Drest\Router\RouterInterface)
		{

		}
		return $this->router;
	}

	/**
	 *
	 * Set the router object
	 * @param Drest\Router $router - allows you to work with a single router across your entire app
	 */
	public function setRouter($router)
	{
		$this->router = $router;
	}


    /**
     * Gets the repository for an entity class.
     *
     * @param Drest\Mapping\ClassMetaData $classMetaData of the entity.
     * @return EntityRepository The repository class.
     */
    public function getRepository(Mapping\ClassMetaData $classMetaData)
    {
    	$repository = $this->em->getRepository($classMetaData->getClassName());
    	if (!$repository instanceof Repository)
    	{
    		throw DrestException::entityRepositoryNotAnInstanceOfDrestRepository($classMetaData->getClassName());
    	}

        return $repository;
    }



}