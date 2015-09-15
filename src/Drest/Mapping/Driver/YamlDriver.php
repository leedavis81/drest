<?php

namespace Drest\Mapping\Driver;

use Doctrine\Common\Annotations;
use Drest\DrestException;
use Drest\Mapping\Annotation;
use Drest\Mapping;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * The YamlDriver reads a configuration file (config.yaml) rather than annotations.
 */
class YamlDriver extends PhpDriver
{

    public function __construct($paths)
    {
        parent::__construct($paths);
    }

    /**
     * Factory method for the Annotation Driver
     *
     * @param  array|string $paths
     * @return self
     */
    public static function create($paths = [])
    {
        return new self($paths);
    }

    /**
     * Get all the metadata class names known to this driver.
     * @return array
     * @throws DrestException
     * @throws DriverException
     */
    public function getAllClassNames()
    {
        if (empty($this->classes)) {
            if (empty($this->paths)) {
                throw DrestException::pathToConfigFilesRequired();
            }

            $yamlParser = new YamlParser();
            foreach ($this->paths as $path)
            {
                if(!file_exists($path)) {
                    throw DriverException::configurationFileDoesntExist($path);
                }

                $resources = $yamlParser->parse(file_get_contents($path));

                if($resources === false || empty($resources)) {
                    throw DriverException::configurationFileIsInvalid('Yaml');
                }

                $this->classes = array_merge($this->classes, (array) $resources);
            }
        }

        return array_keys($this->classes);
    }
}
