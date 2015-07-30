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

use Drest\ClassGenerator;
use Guzzle\Http\Client;
use Guzzle\Http\Message\RequestInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console;

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
            ->setDefinition(
                [
                    new InputArgument(
                        'endpoint', InputArgument::REQUIRED, 'The location of the drest API endpoint.'
                    ),
                    new InputOption(
                        'dest-path', null, InputOption::VALUE_OPTIONAL,
                        'The path to generate your client classes too. If not provided classes are put in the execution path.'
                    ),
                    new InputOption(
                        'namespace', null, InputOption::VALUE_OPTIONAL,
                        'The namespace you would like applied to the classes. This would be prepended to any existing namespaces the classes have'
                    )
                ]
            )
            ->setHelp(
                <<<EOT
                Generate the classes required to interact with a Drest API endpoint.
Example usage:

        classes:generate http://api.endpoint.com --dest-path "{/home/me/classes}"
EOT
            );
    }

    /**
     * @see Console\Command\Command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $endpoint = $input->getArgument('endpoint');

        if (($endpoint = filter_var($endpoint, FILTER_VALIDATE_URL)) === false) {
            throw new \Exception('Invalid endpoint URI provided');
        }

        $client = new Client($endpoint);
        $request = $client->createRequest(
            RequestInterface::OPTIONS,
            null,
            [ClassGenerator::HEADER_PARAM => 'true']
        );

        $response = $client->send($request);
        if (!$response->isSuccessful()) {
            throw new \Exception('Invalid response from provided endpoint.');
        }

        // Process destination directory
        if (($path = $input->getOption('dest-path')) === null) {
            $path = getcwd();
        }
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $path = realpath($path);
        if (!file_exists($path)) {
            throw new \Exception('Destination path doesn\'t exist and couldn\'t be created');
        } else {
            if (!is_writable($path)) {
                throw new \Exception('Cannot write to destination path');
            }
        }

        $classes = unserialize($response->getBody(true));
        if (!is_array($classes)) {
            throw new \Exception('Unexpected response from HTTP endpoint. Array of class objects was expected');
        }
        $classes = array_filter(
            $classes,
            function ($item) {
                return (get_class($item) == 'Zend\Code\Generator\ClassGenerator');
            }
        );
        if (sizeof($classes) === 0) {
            throw new \Exception('No classes to be generated');
        }

        $output->write(
            PHP_EOL . sprintf('Generating client classes....') . PHP_EOL
        );

        $clientNamespace = trim($input->getOption('namespace'), '\\');
        foreach ($classes as $class) {
            /* @var \Zend\Code\Generator\ClassGenerator $class */
            $dir = $path;
            $namespace = $class->getNamespaceName();

            if (!empty($namespace)) {
                $dir .= DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, trim($namespace, '\\'));
            }
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $classPath = $dir . DIRECTORY_SEPARATOR . $class->getName() . '.php';

            // Prepend any client supplied namespace
            if (!is_null($clientNamespace)) {
                $class->setNamespaceName($clientNamespace . '\\' . $class->getNamespaceName());
            }

            $handle = @fopen($classPath, 'w');
            if (!is_resource($handle)) {
                throw new \Exception('Unable to create a file handle for client class ' . $classPath);
            }
            fwrite($handle, "<?php\n" . $class->generate());
            fclose($handle);
            $output->write(
                sprintf('Successfully wrote client class "<info>%s</info>"', $classPath) . PHP_EOL
            );
        }

        $output->write(
            PHP_EOL . sprintf('Client classes have been successfully generated at "<info>%s</info>"', $path) . PHP_EOL
        );
    }
}
