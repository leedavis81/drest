<?php

namespace Drest\Tools\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console;


/**
 * Check Drest Production Setting
 */
class CheckProductionSettings extends Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
            ->setName('drest:check-production-settings')
            ->setDescription('Checks the settings used are suitable for a production environment.')
            ->setHelp(<<<EOT
Checks the settings used are suitable for a production environment
EOT
            );
    }

    /**
     * @see Console\Command\Command
     * @todo: Implement a production ready check
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $drm = $this->getHelper('drm')->getDrestManager()
        // $input->getArgument('xx');


        $message = 'This is some output';

        $output->write($message);
    }
}
