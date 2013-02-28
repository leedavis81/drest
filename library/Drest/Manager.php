<?php


namespace Drest;

use Doctrine\Common\EventManager;

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
    protected function __construct(Doctrine\ORM\EntityManager $em, Configuration $config, EventManager $eventManager)
    {
    	$this->em 			= $em;
        $this->config       = $config;
        $this->eventManager = $eventManager;

        $metadataFactoryClassName = $config->getClassMetadataFactoryName();

        $this->metadataFactory = new $metadataFactoryClassName;
        $this->metadataFactory->setEntityManager($this);
        $this->metadataFactory->setCacheDriver($this->config->getMetadataCacheImpl());

    }


    /**
     * Static call to create the Drest Manager instance
     *
     * @param unknown_type $config
     * @param unknown_type $eventManager
     */
	public static function create(Doctrine\ORM\EntityManager $em, Configuration $config, EventManager $eventManager = null)
	{
		// Run some configuration checks
		if ( ! $config->getMetadataDriverImpl()) {
            throw DrestException::missingMappingDriverImpl();
        }

        return new self($em, $config, $eventManager);
	}


	/**
	 * Dispatches the response
	 */
	public function dispatch()
	{
	}

	/**
	 *
	 * Get the request object
	 * @return Symfony\Component\HttpFoundation\Request $request
	 */
	public function getRequest()
	{
		if (!$this->request instanceof Drest\Request)
		{

		}
		return $this->request;
	}

	/**
	 *
	 * Set the request object
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function setRequest(\Symfony\Component\HttpFoundation\Request $request)
	{
		$this->request = $request
	}


	/**
	 * Get the router object
	 * @return Symfony\Component\Routing\Router $router
	 */
	public function getRouter()
	{
		return $this->router;
	}


    /**
     * Gets the repository for an entity class.
     *
     * @param string $entityName The name of the entity.
     * @return EntityRepository The repository class.
     */
    public function getRepository($entityName)
    {
        $entityName = ltrim($entityName, '\\');

        if (isset($this->repositories[$entityName])) {
            return $this->repositories[$entityName];
        }

        $metadata = $this->getClassMetadata($entityName);
        $repositoryClassName = $metadata->customRepositoryClassName;

        if ($repositoryClassName === null) {
            $repositoryClassName = $this->config->getDefaultRepositoryClassName();
        }

        $repository = new $repositoryClassName($this, $metadata);

        $this->repositories[$entityName] = $repository;

        return $repository;
    }




    /**
     * Factory method to create EntityManager instances.
     *
     * @param mixed $conn An array with the connection parameters or an existing
     *      Connection instance.
     * @param Configuration $config The Configuration instance to use.
     * @param EventManager $eventManager The EventManager instance to use.
     * @return EntityManager The created EntityManager.
     */
    public static function create($conn, Configuration $config, EventManager $eventManager = null)
    {
        if ( ! $config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        switch (true) {
            case (is_array($conn)):
                $conn = \Doctrine\DBAL\DriverManager::getConnection(
                    $conn, $config, ($eventManager ?: new EventManager())
                );
                break;

            case ($conn instanceof Connection):
                if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
                     throw ORMException::mismatchedEventManager();
                }
                break;

            default:
                throw new \InvalidArgumentException("Invalid argument: " . $conn);
        }

        return new EntityManager($conn, $config, $conn->getEventManager());
    }

}