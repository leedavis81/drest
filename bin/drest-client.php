<?php
require_once '../vendor/autoload.php';

$cli = new \Symfony\Component\Console\Application('Drest Client Command Line Interface Tool', Drest\Version::VERSION);
$cli->setCatchExceptions(true);
$cli->addCommands(array(
	// Drest Commands
    new Drest\Tools\Console\Command\GenerateClasses(),
));
$cli->run();