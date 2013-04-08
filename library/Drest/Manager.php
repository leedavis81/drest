<?php

use Drest\DrestException;
namespace Drest;


use Doctrine\Common\Annotations\AnnotationRegistry;

use Doctrine\Common\Annotations\Annotation;

use Doctrine\Common\EventManager,
	Doctrine\ORM\EntityManager,
	Doctrine\Common\Annotations\AnnotationReader,
	Metadata\MetadataFactory,
    Metadata\MetadataFactoryInterface,
	Drest\Request,
	Drest\Repository;



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
                new AnnotationReader()
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
    	foreach ($this->em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames() as $class)
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
	 * Dispatches the response
	 */
	public function dispatch()
	{

		// @todo: continue to try testing hooking un custom annotationsdriver into doctrine orm. Would be nicer to piggyback on refl caching mechanisms
//		$cmf = $this->em->getMetadataFactory();
//		$class = $cmf->getMetadataFor('Entities\User');
//		var_dump($class); die;

	    // Get all classnames registered to the doctrine metadata driver
	    // var_dump($this->em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames()); die;


		// Perform a match based on the current URL / Header / Params - remember to include HTTP VERB checking when performing a matched() call
        $service = $this->getMatchedRoute();

        // Determine the requested format



//		$router = new \Symfony\Component\Routing\RouteCollection();
//		$annoRouting = new \Symfony\Component\Routing\Loader\AnnotationClassLoader($reader);
//		$annoRouting->load($class);
//
//		$b = new \Symfony\Component\Routing\Loader\AnnotationDirectoryLoader($locator, $loader)
//
//		$router->a


		// Echo the reponse object
		//echo $this->getResponse($matchedEntity);
	}

	/**
	 * Runs through all the registered routes and returns a single match
	 * @throws DrestException if no routes are found, or there are multiple matches
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
	 * @return Drest\Request $request
	 */
	public function getRequest()
	{
		if (!$this->request instanceof Request)
		{
			$this->request = Request::create();
		}
		return $this->request;
	}

	/**
	 *
	 * Set the request object
	 * @param \Drest\Request $request
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}


	/**
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
     * @param string $entityName The name of the entity.
     * @return EntityRepository The repository class.
     */
    public function getRepository($entityName)
    {

    	$repository = $this->em->getRepository($entityName);
    	if (!$repository instanceof Repository)
    	{
    		throw DrestException::entityRepositoryNotAnInstanceOfDrestRepository();
    	}

    	// Inject the request object into the extended repository


        return $repository;
    }



}