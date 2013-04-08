<?php


namespace Drest\Mapping\Driver;


use Drest\DrestException;

use	Doctrine\Common\Annotations,
    Doctrine\Common\Persistence\Mapping\Driver as PersistenceDriver,
    Metadata\Driver\DriverInterface,
	Drest\Mapping,
	Drest\Mapping\Annotation;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 * Doesn't require paths / file extensions as entities are pull from the doctrine entity manager
 */
class AnnotationDriver implements DriverInterface
{

	/**
	 * Annotations reader
	 * @var Doctrine\Common\Annotations\AnnotationReader $reader
	 */
    private $reader;

    public function __construct(Annotations\AnnotationReader $reader, $paths = array())
    {
        $this->reader = $reader;
    }


	public static function registerAnnotations()
	{
		Annotations\AnnotationRegistry::registerFile( __DIR__ . '/DrestAnnotations.php');
	}

	/**
	 * @param Doctrine\Common\Annotations\Reader $reader - Can be a cached / uncached reader instance
	 * @return Doctrine\ORM\Mapping\Driver\DriverChain $driverChain
	 * @deprecated Can't test registering an annotation driver into the driver chain as driver requires ClassMetadataInfo as second arg on loadMetadataForClass() - which appears ORM specific
	 */
	public static function registerMapperIntoDriverChain(Annotations\Reader $reader)
	{
		// Include the defined annotations
		Annotations\AnnotationRegistry::registerFile( __DIR__ . '/DrestAnnotations.php');

		$driverChain = new PersistenceDriver\MappingDriverChain();
		// Add Drest annotation driver to the driver chain
		$drestDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader, array(
		            __DIR__.'/../Annotation'
		));
		$driverChain->addDriver($drestDriver, 'Drest');

		return $driverChain;
	}



	/**
	 * Load metadata for a class name
	 * @param object|string $className - Pass in either the class name, or an instance of that class
	 * @return Drest\Mapping\ClassMetaData $metaData - return null if metadata couldn't be populated from annotations
	 */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        foreach ($this->reader->getClassAnnotations($class) as $annotatedObject)
        {
        	if ($annotatedObject instanceof Annotation\Resource)
        	{
        	    if ($annotatedObject->services === null)
        	    {
        	        throw DrestException::annotatedResourceRequiresAtLeastOneServiceDefinition($class->name);
        	    }

        	    $metadata = new Mapping\ClassMetadata($class->name);

        	    foreach ($annotatedObject->services as $service)
        	    {
        	        $serviceMetaData = new Mapping\ServiceMetaData();

        	        // Set name
        	        $service->name = preg_replace("/[^a-zA-Z0-9_\s]/", "", $service->name);
        	        if ($service->name == '')
        	        {
                        throw DrestException::serviceNameIsEmpty();
        	        }
        	        if ($metadata->getServicesMetaData($service->name) !== false)
        	        {
        	            throw DrestException::serviceAlreadyDefinedWithName($class->name, $service->name);
        	        }
                    $serviceMetaData->setName($service->name);

                    // Set verbs (will throw if invalid)
        	        if (isset($service->verbs))
        	        {
        	            $serviceMetaData->setVerbs($service->verbs);
        	        }

        	        // Set content type (will throw if invalid)
        	        $serviceMetaData->setContentType($service->content);

        	        // Add the route
        	        /** @todo: run validation checks on route syntax? */
        	        $serviceMetaData->setRoutePattern($service->route_pattern);

        	        // Add repository method
        	        $serviceMetaData->setRepositoryMethod($service->repository_method);


                    $metadata->addServiceMetaData($serviceMetaData);
        	    }

        	}
        }

        return (isset($metadata)) ? $metadata : null;
    }


    /**
     * Factory method for the Annotation Driver
     *
     * @param AnnotationReader $reader
     * @param array|string $paths
     * @return AnnotationDriver
     */
    static public function create(Annotations\AnnotationReader $reader = null, $paths = array())
    {
        if ($reader == null) {
            $reader = new Annotations\AnnotationReader();
        }

        return new self($reader, $paths);
    }
}
