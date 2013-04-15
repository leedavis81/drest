<?php

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



$helperSet =  new \Symfony\Component\Console\Helper\HelperSet(array(
		'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
		'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em),
        'dialog' => new \Symfony\Component\Console\Helper\DialogHelper()
));