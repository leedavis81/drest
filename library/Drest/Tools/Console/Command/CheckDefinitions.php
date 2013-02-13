<?php

namespace Drest\Tools\Console\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console,
    Symfony\Component\Console\Command\Command;


/**
 * Check Drest definitions
 */
class CheckDefinitions extends Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('drest:check-definitions')
        ->setDescription('Checks the definitions used are valid.')
        ->setHelp(<<<EOT
Validate that the drest definitions are correct
EOT
        );
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $drm = $this->getHelper('drm')->getDrestManager()
		// $input->getArgument('xx');


        $message = 'This is some output';

        $output->write($message);
    }
}
