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
class GenerateClasses extends Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('classes:generate')
        ->setDescription('Generate client classes to interact with a Drest endpoint.')
        ->setDefinition(array(
            new InputArgument(
                'endpoint', InputArgument::REQUIRED, 'The location of the drest API endpoint.'
            ),
            new InputArgument(
                'dest-path', InputArgument::OPTIONAL, 'The path to generate your client classes too. If not provided classes are put in the execution path'
            )
        ))
        ->setHelp(<<<EOT
Generate the classes required to interact with a Drest API endpoint.
Example usage:

		classes:generate endpoint "{http://external.api}" dest-path "{/home/me/classes}"
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
