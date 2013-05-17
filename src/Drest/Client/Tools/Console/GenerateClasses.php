<?php
namespace Drest\Client\Tools\Console\Command;

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
            ->setName('drest-client:generate-classes')
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

        $message = 'Crack on and generate the drest client classes';

        // todo possibly provide a switch to see if the classes need updating
        $output->write($message);
    }
}
