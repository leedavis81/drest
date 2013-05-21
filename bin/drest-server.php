<?php

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'server-config.php';


if (!file_exists($configFile) || !is_readable($configFile))
{
	trigger_error('Unable to read configuration file: ' . $configFile);
}

require_once $configFile;


$helperSet = (isset($helperSet)) ? $helperSet : new \Symfony\Component\Console\Helper\HelperSet();


$cli = new \Symfony\Component\Console\Application('Drest Server Command Line Interface Tool', Drest\Version::VERSION);
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);
$cli->addCommands(array(
	// Drest Commands
    new Drest\Tools\Console\Command\CheckDefinitions(),
    new Drest\Tools\Console\Command\CheckProductionSettings()
));
$cli->run();