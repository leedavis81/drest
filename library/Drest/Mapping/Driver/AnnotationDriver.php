<?php


namespace Drest\Mapping\Driver;



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
        		/**
					object(Drest\Mapping\Annotation\Resource)#48 (4) {
					  ["name"]=>
					  string(7) "testing"
					  ["content"]=>
					  string(6) "single"
					  ["route"]=>
					  object(Drest\Mapping\Annotation\Route)#47 (3) {
					    ["name"]=>
					    string(11) "users_route"
					    ["pattern"]=>
					    string(7) "/user/*"
					    ["verbs"]=>
					    array(1) {
					      [0]=>
					      string(3) "GET"
					    }
					  }
					  ["writers"]=>
					  array(2) {
					    [0]=>
					    string(3) "Xml"
					    [1]=>
					    string(4) "Json"
					  }
					}
        		 */
        		//Drest\Mapping\Annotation\Resource
				var_dump($annotatedObject);

//        		$annotatedObject->route['name']
//        		$annotatedObject->route['pattern']
//        		$annotatedObject->route['verbs']

        		//$metadata->addRoute($name)
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
