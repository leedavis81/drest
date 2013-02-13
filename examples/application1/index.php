<?php
require_once '../../vendor/autoload.php';


use Drest\Configuration;
use Symfony\Component\Console\Application;


$config = new Configuration();
$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());



// Create an example doctrine application
$ormConfig = new \Doctrine\ORM\Configuration();

// Do proxy stuff
$ormConfig->setProxyDir(__DIR__ . '/Entities/Proxies');
$ormConfig->setProxyNamespace('Entities\Proxies');
$ormConfig->setAutoGenerateProxyClasses(true);

$driver = $ormConfig->newDefaultAnnotationDriver(array(__DIR__ . '/Entities'));
$ormConfig->setMetadataDriverImpl($driver);
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