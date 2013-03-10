<?php

namespace Drest;


use Doctrine\Common\EventManager,
	Doctrine\ORM\EntityManager,
	Doctrine\Common\Annotations\AnnotationReader,
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
	 * Drest router
	 * @var Symfony\Component\Routing\Router $router
	 */
	protected $router;

	/**
	 * Drest request object
	 * @var Symfony\Component\HttpFoundation\Request $request
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
		// Fetch the annotation information
//		$metaData = $this->em->getMetadataFactory()->getAllMetadata();
//		var_dump($metaData);


		$reader = new AnnotationReader();

		$driver = \Drest\Mapping\Driver\AnnotationDriver::create($reader);


		$driver->loadMetadataForClass('Entities\User');






		// Add all the defined routes to the supplied router object



//		$router = new \Symfony\Component\Routing\RouteCollection();
//		$annoRouting = new \Symfony\Component\Routing\Loader\AnnotationClassLoader($reader);
//		$annoRouting->load($class);
//
//		$b = new \Symfony\Component\Routing\Loader\AnnotationDirectoryLoader($locator, $loader)
//
//		$router->a
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
	 * @return Drest\Router $router
	 */
	public function getRouter()
	{
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