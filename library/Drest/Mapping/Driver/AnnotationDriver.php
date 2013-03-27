<?php


namespace Drest\Mapping\Driver;



use Drest\DrestException;

use	Doctrine\Common\Annotations,
	Drest\Mapping,
	Drest\Mapping\Annotation;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 */
class AnnotationDriver
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

		$driverChain = new Driver\DriverChain();
		// Add Drest annotation driver to the driver chain
		$drestDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader, array(
		            __DIR__.'/../Annotation'
		));
		$driverChain->addDriver($drestDriver, 'Drest');

		return $driverChain;
	}


//
//
//    public function loadMetadataForClass(\ReflectionClass $class)
//    {
//        $metadata = new ClassMetadata($class->name);
//
//        $hasMetadata = false;
//        foreach ($this->reader->getClassAnnotations($class) as $annot) {
//            if ($annot instanceof ObjectRoute) {
//                $hasMetadata = true;
//                $metadata->addRoute($annot->type, $annot->name, $annot->params);
//            }
//        }
//
//        return $hasMetadata ? $metadata : null;
//    }


	/**
	 * Load metadata for a class name
	 * @param object|string $className - Pass in either the class name, or an instance of that class
	 * @return Drest\Mapping\ClassMetaData $metaData - return null if metadata couldn't be populated from annotations
	 */
    public function loadMetadataForClass($className)
    {
        $metadata = new Mapping\ClassMetadata($class->name);

		if (is_object($className))
		{
        	$refClass = new \ReflectionClass(get_class($className));
		} elseif (is_string($className))
		{
			$refClass = new \ReflectionClass($className);
		}

        foreach ($this->reader->getClassAnnotations($refClass) as $annotatedObject)
        {
        	if ($annotatedObject instanceof Annotation\Resource)
        	{
        	    if ($annotatedObject->services === null)
        	    {
        	        throw DrestException::annotatedResourceRequiresAtLeastOneServiceDefinition($className);
        	    }

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
        	            throw DrestException::serviceAlreadyDefinedWithName($className, $service->name);
        	        }
                    $serviceMetaData->setName($service->name);

                    // Set verbs (will throw if invalid)
        	        if (isset($service->verbs))
        	        {
        	            $serviceMetaData->addVerbs($service->verbs);
        	        }

        	        // Set content type (will throw if invalid)
        	        $serviceMetaData->setContentType($service->content);

        	        // Add the route
        	        /** @todo: run validation checks on route syntax? */
        	        $serviceMetaData->addRoute($service->route);

        	        // Add repository method
        	        //$serviceMetaData->addRepositoryMethod($service->repository_method);


                    $metadata->addServiceMetaData($serviceMetaData);
        	    }

        		//Drest\Mapping\Annotation\Resource
				var_dump($annotatedObject);

        	}
        }



//        if ($classAnnotations) {
//            foreach ($classAnnotations as $key => $annot) {
//                if ( ! is_numeric($key)) {
//                    continue;
//                }
//
//                $classAnnotations[get_class($annot)] = $annot;
//            }
//        }

        // Evaluate Entity annotation
//        if (isset($classAnnotations['Doctrine\ORM\Mapping\Entity'])) {
//            $entityAnnot = $classAnnotations['Doctrine\ORM\Mapping\Entity'];
//            if ($entityAnnot->repositoryClass !== null) {

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
