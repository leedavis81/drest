<?php

namespace Drest\Mapping\Driver;


use Doctrine\Common\Annotations\Annotation;

use	Doctrine\Common\Annotations,
	Doctrine\ORM\Mapping\Driver;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 */
class AnnotationDriver
{


	/**
	 * @param Doctrine\Common\Annotations\Reader $reader - Can be a cached / uncached reader instance
	 * @return Doctrine\ORM\Mapping\Driver\DriverChain $driverChain
	 */
	public static function registerMapperIntoDriverChain(Annotations\Reader $reader)
	{
		// Include the defined annotations
		Annotations\AnnotationRegistry::registerFile( __DIR__ . '/DrestAnnotations.php');

		$driverChain = new Driver\DriverChain();
		// Add Drest annotation driver to the driver chain
		$drestDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader, array(
		            __DIR__.'/Translatable/Entity',
		            __DIR__.'/Loggable/Entity',
		            __DIR__.'/Tree/Entity',
		));
		$driverChain->addDriver($drestDriver, 'Drest');

		return $driverChain;
	}


    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        /* @var $metadata \Doctrine\ORM\Mapping\ClassMetadataInfo */
        $class = $metadata->getReflectionClass();
        if ( ! $class) {
            // this happens when running annotation driver in combination with
            // static reflection services. This is not the nicest fix
            $class = new \ReflectionClass($metadata->name);
        }

        $classAnnotations = $this->reader->getClassAnnotations($class);

        if ($classAnnotations) {
            foreach ($classAnnotations as $key => $annot) {
                if ( ! is_numeric($key)) {
                    continue;
                }

                $classAnnotations[get_class($annot)] = $annot;
            }
        }

        // Evaluate Entity annotation
//        if (isset($classAnnotations['Doctrine\ORM\Mapping\Entity'])) {
//            $entityAnnot = $classAnnotations['Doctrine\ORM\Mapping\Entity'];
//            if ($entityAnnot->repositoryClass !== null) {

    }


    /**
     * Factory method for the Annotation Driver
     *
     * @param array|string $paths
     * @param AnnotationReader $reader
     * @return AnnotationDriver
     */
    static public function create($paths = array(), Annotations\AnnotationReader $reader = null)
    {
        if ($reader == null) {
            $reader = new Annotations\AnnotationReader();
        }

        return new self($reader, $paths);
    }
}
