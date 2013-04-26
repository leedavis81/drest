<?php
namespace Drest;


use Doctrine\Common\EventManager,
    Doctrine\Common\Annotations\Annotation,
    Doctrine\Common\Annotations\AnnotationRegistry,
    Doctrine\Common\Annotations\AnnotationReader,
	Doctrine\ORM\EntityManager,

	Drest\Mapping\MetadataFactory,

	Drest\Request,
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
	 * @var array contains array of service classes of instance \Drest\Service\AbstractService
	 */
	protected $services;


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

        // @todo: standardise the error response, current defaults to the framework impl - return error in format (writer) requested

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
		try {
            $route = $this->getMatchedRoute(true);
		} catch (\Exception $e)
		{
		    if ($e instanceof NoMatchException && $this->doOptionsCheck())
		    {
                return $this->getResponse();
		    } else
		    {
		        throw $e;
		    }
		}

        // Check exposure field definitions, if non are set use the default depth setting
        //$route = $this->setupExposeFields($route);

        // Set paramaters matched on the route to the request object
        $this->request->setRouteParam($route->getRouteParams());

        // Get the service class
        $service = $this->getService($route->getClassMetaData()->getClassName());

        // Set the matched service object into the service class
        $service->setMatchedRoute($route);

        // This is a new request, clear out out any existing old data on the cached service object
        $service->clearData();

        // Use a default call if the DefaultService class is being used (allow for extension)
        $callMethod = (get_class($service) === 'Drest\Service\DefaultService') ? $service->getDefaultMethod() : $route->getCallMethod();
        if (!method_exists($service, $callMethod))
        {
            throw DrestException::unknownServiceMethod(get_class($service), $callMethod);
        }
        $service->$callMethod();

        // Only run the writers if the body hasn't already been written too
        if ($this->response->getBody() == '')
        {
            // Pass the results to a writer
            $data = $service->getData();
            if ($this->response->getStatusCode() == 200 && !empty($data))
            {
                $this->execWriter($route, $data);
            }
        }

        return $this->getResponse();
	}

	/**
	 * Set the default exposure fields using the configured depth default
	 * @param \Drest\Mapping\RouteMetaData $route
	 * @return \Drest\Mapping\RouteMetaData $route
	 */
	protected function setupExposeFields(\Drest\Mapping\RouteMetaData $route)
	{
        $expose = $route->getExpose();
        if (!empty($expose))
        {
            return $route;
        }

        // expose={"username", "email_address", "profile" : {"id", "lastname", "addresses" : {"address"}}, "phone_numbers" : {"number"}}
        array(
            'username',
            'email_address',
            'profile' => array(
                'id',
                'lastname',
				'addresses'
            )
        );


        $fields = array();
        $depth = $this->config->getDefaultExposureDepth();
        $depth = 3;
        $fields = $this->processDefaultExposeDepth($fields, $route->getClassMetaData()->getClassName(), $depth);

        echo '********RESULT***********' . PHP_EOL;
        var_dump($fields);

        return $route;
	}


	/**
	 * Recursive function to generate default expose columns
	 */
	protected function processDefaultExposeDepth(&$fields, $class, $depth = 0)
	{
        if ($depth > 0)
        {
            $metaData = $this->em->getClassMetadata($class);
            $fields = $metaData->getColumnNames();

            if (($depth - 1) > 0)
            {
                foreach ($metaData->getAssociationMappings() as $key => $assocMapping)
                {
                    $this->processDefaultExposeDepth($fields[$key], $assocMapping['targetEntity'], --$depth);
                }
            }
            $this->processDefaultExposeDepth($fields, $class, --$depth);
        }
        return $fields;
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

        $this->getResponse()->setHttpHeader('Allow', implode(', ', $verbs));
        return true;
	}

    /**
     * @todo: split this up, one method for detections, another for performing the write
     * Detect the writer to be applied, pass in the data and write the content to the response object
     * @param Drest\Mapping\RouteMetaData $route
     * @param array $data
     */
	protected function execWriter(Mapping\RouteMetaData $route, array $data = array())
	{
	    $writers = $route->getClassMetaData()->getWriters();
	    if (empty($writers))
	    {
	        $writers = $this->config->getDefaultWriters();
	        if (empty($writers))
	        {
	            throw DrestException::noWritersSetForRoute($route);
	        }
	    }

        $writerFound = false;
	    foreach ($writers as $writer)
	    {
	        if (!is_object($writer))
	        {
	            // Check if the class is namespaced, if so instantiate from root
	            $className = (strstr($writer, '\\') !== false) ? '\\' . ltrim($writer, '\\') : $writer;
                $className = (!class_exists($className)) ? '\\Drest\\Writer\\' . ltrim($className, '\\') : $className;
	            if (!class_exists($className))
	            {
	                throw DrestException::unknownWriterClass($writer);
	            }
	            $writer = new $className();
	        }
	        if (!$writer instanceof Writer\AbstractWriter)
	        {
	            throw DrestException::writerMustBeInstanceOfDrestWriter();
	        }

            if ($this->detectContentWriter($writer))
            {
                $this->response->setBody(trim($writer->write($data)));
                $this->response->setHttpHeader('Content-Type', $writer->getContentType());
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
	 * @param boolean $matchVerb - Whether you want to match the route using the request HTTP verb
	 * @throws NoMatchException if no routes are found
	 * @throws MultipleRoutesException If there are multiple matches
	 * @return Drest\Mapping\RouteMetaData $route
	 */
	protected function getMatchedRoute($matchVerb = true)
	{
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
     * Get the service class for this entity
     * @param string $entityName
     * @return Drest\Service\AbstractService $service
     * @throws DrestException if defined service class is not an instance of Drest\Service\AbstractService
     */
	public function getService($entityName)
	{
        $entityName = ltrim($entityName, '\\');
	    if (isset($this->services[$entityName]))
	    {
	        return $this->services[$entityName];
	    }

	    $classMetaData = $this->metadataFactory->getMetadataForClass($entityName);

	    $serviceClassName = '\\' . $classMetaData->getServiceClassName();

	    $serviceClassName = $classMetaData->getServiceClassName();
	    if ($serviceClassName !== null)
	    {
            $serviceClassName = (strpos($serviceClassName, '\\') === 0) ? $serviceClassName : '\\' . $serviceClassName;
	    } else
	    {
            $serviceClassName = $this->config->getDefaultServiceClass();
	    }

	    $service = new $serviceClassName($this->em, $this);

	    if (!$service instanceof Service\AbstractService)
	    {
	        throw DrestException::entityServiceNotAnInstanceOfDrestService($classMetaData->getClassName());
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