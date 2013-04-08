<?php
use Drest\Configuration;



error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 'On');

$loader = require '../../../vendor/autoload.php';

// Add the entities namespace to the loader
$loader->add('Entities', __DIR__.'/../');


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


$pathToEntities = array(__DIR__ . '/../Entities');

$ORMDriver = $ormConfig->newDefaultAnnotationDriver($pathToEntities, false);


Drest\Mapping\Driver\AnnotationDriver::registerAnnotations();

//$driverChain = Drest\Mapping\Driver\AnnotationDriver::registerMapperIntoDriverChain($cachedAnnotationReader);

// Add the Doctrine ORM driver to the driver chain we've just created (including its namespace)
//$driverChain->addDriver($ORMDriver, 'Entities');

// add a driver chain to the ORM config
//$ormConfig->setMetadataDriverImpl($driverChain);
$ormConfig->setMetadataDriverImpl($ORMDriver);

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



try
{
	$drestConfig = new Configuration();
	$drestConfig->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
	$drestManager = \Drest\Manager::create($em, $drestConfig);

} catch (Exception $e) {
	echo $e->getMessage() . PHP_EOL;
	echo $e->getTraceAsString() . PHP_EOL;
}


$drestManager->dispatch();





