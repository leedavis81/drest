<?php


namespace Drest\Mapping\Driver;



use Drest\DrestException;

use	Doctrine\Common\Annotations,
	Drest\Mapping\ClassMetadata,
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
        $metadata = new ClassMetadata($class->name);

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
        	    foreach ($annotatedObject->services as $service)
        	    {
            	    if (!isset($service->route))
    				{
    				    throw DrestException::annotatedServiceRequiresRouteDefinition($className);
    				}
        	    }
        		/**
                    object(Drest\Mapping\Annotation\Resource)#47 (1) {
                      ["services"]=>
                      array(2) {
                        [0]=>
                        object(Drest\Mapping\Annotation\Service)#51 (5) {
                          ["name"]=>
                          string(10) "user_route"
                          ["content"]=>
                          string(7) "element"
                          ["verbs"]=>
                          NULL
                          ["writers"]=>
                          array(2) {
                            [0]=>
                            string(3) "Xml"
                            [1]=>
                            string(4) "Json"
                          }
                          ["route"]=>
                          object(Drest\Mapping\Annotation\Route)#50 (3) {
                            ["pattern"]=>
                            string(9) "/user/:id"
                            ["repositoryMethod"]=>
                            string(7) "getUser"
                            ["verbs"]=>
                            array(1) {
                              [0]=>
                              string(3) "GET"
                            }
                          }
                        }
                        [1]=>
                        object(Drest\Mapping\Annotation\Service)#49 (5) {
                          ["name"]=>
                          string(11) "users_route"
                          ["content"]=>
                          string(10) "collection"
                          ["verbs"]=>
                          NULL
                          ["writers"]=>
                          array(2) {
                            [0]=>
                            string(3) "Xml"
                            [1]=>
                            string(4) "Json"
                          }
                          ["route"]=>
                          object(Drest\Mapping\Annotation\Route)#48 (3) {
                            ["pattern"]=>
                            string(6) "/users"
                            ["repositoryMethod"]=>
                            string(8) "getUsers"
                            ["verbs"]=>
                            array(1) {
                              [0]=>
                              string(3) "GET"
                            }
                          }
                        }
                      }
                    }
        		 */
        		//Drest\Mapping\Annotation\Resource
				var_dump($annotatedObject);

//        		$annotatedObject->route['name']
//        		$annotatedObject->route['pattern']
//        		$annotatedObject->route['verbs']


        		$metadata->addRoute($annotatedObject->route);
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
