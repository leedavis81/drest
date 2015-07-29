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
            ->setName('config:production-ready')
            ->setDescription('Checks the settings used are suitable for a production environment.')
            ->setHelp(
                <<<EOT

Checks the settings used are suitable for a production environment.
Notifications are given for using debug mode, or using a bad cache implementation.

Example usage:
php drest-server.php config:production-ready
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
            $drm->getConfiguration()->ensureProductionSettings();
            $output->write('Production settings OK' . PHP_EOL);
        } catch (\Exception $e) {
            $output->write(PHP_EOL . $e->getMessage() . PHP_EOL);
        }
    }
}
