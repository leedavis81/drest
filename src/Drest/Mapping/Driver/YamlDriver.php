<?php

namespace Drest\Mapping\Driver;

use Doctrine\Common\Annotations;
use Drest\Configuration;
use Drest\DrestException;
use Drest\Mapping\Annotation;
use Drest\Mapping;
use Drest\Mapping\RouteMetaData;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * The YamlDriver reads a configuration file (config.yaml) rather than utilizing annotations.
 */
class YamlDriver extends PhpDriver
{

    public function __construct($paths, $yaml)
    {
        $filename = self::$configuration_filepath . DIRECTORY_SEPARATOR . self::$configuration_filename;

        parent::__construct($paths, $filename);

        if(!file_exists($filename)) {
            throw new \RuntimeException('The configuration file does not exist at this path: ' . $filename);
        } 

        $parsed = $yaml->parse(file_get_contents($filename));

        if($parsed == false) {
            throw new \RuntimeException('The configuration file does not have valid YAML: ' . $filename);
        }

        $this->classes = $parsed;
        
    }

    /**
     * Factory method for the Annotation Driver
     *
     * @param  array|string $paths
     * @return YamlDriver
     */
    public static function create($paths = [])
    {
        $yaml = new Parser();
        return new self($paths, $yaml);
    }

}
