<?php
require_once '../../vendor/autoload.php';


use Drest\Configuration;
use Symfony\Component\Console\Application;


$config = new Configuration();
$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());



// Create an example doctrine application
$ormConfig = new \Doctrine\ORM\Configuration();



// globally used cache driver, in production use APC or memcached
$cache = new Doctrine\Common\Cache\ArrayCache;
// standard annotation reader
$annotationReader = new Doctrine\Common\Annotations\AnnotationReader;
$cachedAnnotationReader = new Doctrine\Common\Annotations\CachedReader(
    $annotationReader, // use reader
    $cache // and a cache driver
);

// create a driver chain for metadata reading
$driverChain = new Doctrine\ORM\Mapping\Driver\DriverChain();
// load superclass metadata mapping only, into driver chain
// also registers Gedmo annotations.NOTE: you can personalize it



    /**
     * Add a new default annotation driver with a correctly configured annotation reader. If $useSimpleAnnotationReader
     * is true, the notation `@Entity` will work, otherwise, the notation `@ORM\Entity` will be supported.
     *
     * @param array $paths
     * @param bool $useSimpleAnnotationReader
     * @return AnnotationDriver
     */
//    public function newDefaultAnnotationDriver($paths = array(), $useSimpleAnnotationReader = true)
//    {
//        AnnotationRegistry::registerFile(__DIR__ . '/Mapping/Driver/DoctrineAnnotations.php');
//
//        if ($useSimpleAnnotationReader) {
//            // Register the ORM Annotations in the AnnotationRegistry
//            $reader = new SimpleAnnotationReader();
//            $reader->addNamespace('Doctrine\ORM\Mapping');
//            $cachedReader = new CachedReader($reader, new ArrayCache());
//
//            return new AnnotationDriver($cachedReader, (array) $paths);
//        }
//
//        return new AnnotationDriver(
//            new CachedReader(new AnnotationReader(), new ArrayCache()),
//            (array) $paths
//        );
//    }



$ORMDriver = $ormConfig->newDefaultAnnotationDriver(array(__DIR__ . '/Entities'));
$driverChain->addDriver($ORMDriver);


// Instead of adding a single driver to your ORM configuration object, instead create a driver chain and add multiple
// You can still use the same annotations reader

// THIS NEEDS ABSTRACTING INTO THE DREST LIBRARY
$DrestDriver = new Doctrine\ORM\Mapping\Driver\AnnotationDriver($ORMDriver->getReader(), array(
            __DIR__.'/Translatable/Entity',
            __DIR__.'/Loggable/Entity',
            __DIR__.'/Tree/Entity',
));
$driverChain->addDriver($DrestDriver, 'Drest');




// Rather than adding a single driver, add a driver chain
$ormConfig->setMetadataDriverImpl($driverChain);

// Do proxy stuff
$ormConfig->setProxyDir(__DIR__ . '/Entities/Proxies');
$ormConfig->setProxyNamespace('Entities\Proxies');
$ormConfig->setAutoGenerateProxyClasses(true);

$em = \Doctrine\ORM\EntityManager::create(array(
	'host' => 'localhost',
	'user' => 'developer',
	'password' => 'developer',
	'dbname' => 'drest',
	'driver' => 'pdo_mysql'
), $ormConfig);





//$drestConfig = new Configuration();
//
//
//
//
//$request->dispatch();