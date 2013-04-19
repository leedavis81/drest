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

	$drestConfig->setDetectContentOptions(array(
	    Configuration::DETECT_CONTENT_ACCEPT_HEADER,
	    Configuration::DETECT_CONTENT_EXTENSION,
	    Configuration::DETECT_CONTENT_PARAM
    ));

	$drestConfig->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());

	$drestConfig->setDebugMode(true);

	$drestManager = \Drest\Manager::create($em, $drestConfig);

} catch (Exception $e) {
	echo $e->getMessage() . PHP_EOL;
	echo $e->getTraceAsString() . PHP_EOL;
}


echo $drestManager->dispatch();




