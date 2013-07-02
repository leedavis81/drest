<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

chdir(__DIR__);

require_once '../vendor/autoload.php';

$cli = new \Symfony\Component\Console\Application('Drest Client Command Line Interface Tool', Drest\Version::VERSION);
$cli->setCatchExceptions(true);
$cli->addCommands(array(
	// Drest Commands
    new Drest\Tools\Console\Command\GenerateClasses(),
));
$cli->run();