<?php
use Drest\Configuration;
use Drest\Event;

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

$loader = require '../../../vendor/autoload.php';

// Add the entities namespace to the loader
$loader->add('Entities', __DIR__ . '/../');
$loader->add('Action', __DIR__ . '/../');
$loader->add('MyEvents', __DIR__ . '/../');

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

/********************START SETTING UP DREST CONFIGURATION***************************/

$drestConfig = new Configuration();

$drestConfig->setDetectContentOptions(array(
    Configuration::DETECT_CONTENT_HEADER => 'Accept',
    Configuration::DETECT_CONTENT_EXTENSION => true,
    Configuration::DETECT_CONTENT_PARAM => 'format'
));

$drestConfig->setExposureDepth(3);
$drestConfig->setExposeRequestOption(Configuration::EXPOSE_REQUEST_PARAM_GET, 'expose');
$drestConfig->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
//$drestConfig->setDebugMode(true);


$drestConfig->addPathsToConfigFiles($pathToEntities);

// Set up event manager
$evm = new Event\Manager();
//$evm->addEventListener(array('preServiceAction', 'postServiceAction', 'preRouting', 'postRouting', 'preDispatch', 'postDispatch'), new MyEvents\MyEvent());

// Set up the service action registry
$serviceActions = new Drest\Service\Action\Registry();
$serviceActions->register(
    new Action\Custom(),
    ['Entities\User::get_user2', 'Entities\User::get_user3']
);

$emr = \Drest\EntityManagerRegistry::getSimpleManagerRegistry($em);
$drestManager = \Drest\Manager::create($emr, $drestConfig, $evm, $serviceActions);

echo $drestManager->dispatch();

//echo $drestManager->dispatch(null, null, 'Entities\User::get_user', array('id' => 1));

//echo $drestManager->dispatch(new \Zend\Http\PhpEnvironment\Request(), new Zend\Http\PhpEnvironment\Response());