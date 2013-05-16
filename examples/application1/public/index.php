<?php
use Drest\Configuration;


error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 'On');

$loader = require '../../../vendor/autoload.php';

// Add the entities namespace to the loader
$loader->add('Entities', __DIR__.'/../');
$loader->add('Service', __DIR__.'/../');


// Create an example doctrine application
$ormConfig = new \Doctrine\ORM\Configuration();

$pathToEntities = array(__DIR__ . '/../Entities');
$ORMDriver = $ormConfig->newDefaultAnnotationDriver($pathToEntities, false);

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
	    Configuration::DETECT_CONTENT_HEADER => 'Accept',
	    Configuration::DETECT_CONTENT_EXTENSION => true,
	    Configuration::DETECT_CONTENT_PARAM => 'format'
    ));

    $drestConfig->setExposureDepth(3);

    $drestConfig->setExposeRequestOption(Configuration::EXPOSE_REQUEST_PARAM_GET, 'expose');

	$drestConfig->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());

	$drestConfig->setDebugMode(true);

	$drestConfig->addPathsToConfigFiles($pathToEntities);

	$drestManager = \Drest\Manager::create($em, $drestConfig);

} catch (Exception $e) {
	echo $e->getMessage() . PHP_EOL;
	echo $e->getTraceAsString() . PHP_EOL;
}


echo $drestManager->dispatch();

//@todo: add in the ability to dispatch a named route (bypasses routing and fetches based on name)
//echo $drestManager->dispatch(null, null, 'Entities\User::get_user', array('id' => 1));

//echo $drestManager->dispatch(new \Zend\Http\PhpEnvironment\Request(), new Zend\Http\PhpEnvironment\Response());




