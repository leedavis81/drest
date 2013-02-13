<?php
require_once '../vendor/autoload.php';


// Create an example doctrine application (This is where you'd fire up your application bootstrap)
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



$helperSet =  new \Symfony\Component\Console\Helper\HelperSet(array(
		'em' => new \Drest\Tools\Console\Helper\EntityManagerHelper($em),
        'dialog' => new \Symfony\Component\Console\Helper\DialogHelper(),
        'formatter' => new \Symfony\Component\Console\Helper\FormatterHelper()
));