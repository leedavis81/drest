<?php

namespace Drest\Tools\Console\Command;

use Drest\ClassGenerator,
    Symfony\Component\Console\Input\InputArgument,
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
            new InputOption(
                'dest-path', null, InputOption::VALUE_OPTIONAL,
                'The path to generate your client classes too. If not provided classes are put in the execution path.'
            )
        ))
        ->setHelp(<<<EOT
Generate the classes required to interact with a Drest API endpoint.
Example usage:

		classes:generate http://api.endpoint.com --dest-path "{/home/me/classes}"
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

        $endpoint = $input->getArgument('endpoint');

        if (($endpoint = filter_var($endpoint, FILTER_VALIDATE_URL)) === false)
        {
            throw new \Exception('Invalid endpoint URI provided');
        }

        $client = new \Guzzle\Http\Client($endpoint);
        $request = $client->createRequest(
            \Guzzle\Http\Message\RequestInterface::OPTIONS,
            null,
            array(ClassGenerator::HEADER_PARAM => 'true')
        );


        $response = $client->send($request);

        // @todo: Check response (error handling etc)

//        $response = new \Guzzle\Http\Message\Response();

        $classes = unserialize($response->getBody(true));

        foreach ($classes as $class)
        {
            $namespace = $class->getNamespaceName();

            //@todo: pull this from input arg
            $dir = getcwd();
            if (!empty($namespace))
            {
                $dir .= DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, trim($namespace, '\\'));
            }
            if (!is_dir($dir))
            {
                mkdir($dir, 0755, true);
            }

            $classPath = $dir . DIRECTORY_SEPARATOR . $class->getName() . '.php';

            echo 'Writing to: ' . $classPath . PHP_EOL;
            $handle = fopen($classPath, 'w');
            fwrite($handle, "<?php\n" . $class->generate());
            fclose($handle);
        }

        $message = 'This is some output';

        $output->write($message);
    }
}
