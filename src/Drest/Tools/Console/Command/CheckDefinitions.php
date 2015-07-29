<?php
/**
 * This file is part of the Drest package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Lee Davis
 * @copyright Copyright (c) Lee Davis <@leedavis81>
 * @link https://github.com/leedavis81/drest/blob/master/LICENSE
 * @license http://opensource.org/licenses/MIT The MIT X License (MIT)
 */
namespace Drest\Tools\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console;

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
            ->setName('annotation:check')
            ->setDescription('Checks the definitions used in Entity annotations are valid.')
            ->setHelp(
                <<<EOT
                Validate that the drest definitions are correct
EOT
            );
    }

    /**
     * @see Console\Command\Command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $drm \Drest\Manager */
        $drm = $this->getHelper('drm')->getDrestManager();

        try {
            $drm->checkDefinitions();
            $output->write('Syntax check OK' . PHP_EOL);
        } catch (\Exception $e) {
            $output->write(PHP_EOL . $e->getMessage() . PHP_EOL);
        }
    }
}
