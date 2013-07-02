<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

chdir(__DIR__);

$configFile = 'server-config.php';

if (!file_exists($configFile) || !is_readable($configFile))
{
	trigger_error('Unable to read configuration file: ' . $configFile . ' Use server-config.php.dist as a template to create server-config.php.dist');
}

include $configFile;

$helperSet = (isset($helperSet)) ? $helperSet : new \Symfony\Component\Console\Helper\HelperSet();


$cli = new \Symfony\Component\Console\Application('Drest Server Command Line Interface Tool', Drest\Version::VERSION);
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);
$cli->addCommands(array(
	// Drest Commands
//    new Drest\Tools\Console\Command\CheckDefinitions(),
//    new Drest\Tools\Console\Command\CheckProductionSettings()
));
$cli->run();