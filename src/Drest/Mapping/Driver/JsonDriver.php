<?php

namespace Drest\Mapping\Driver;

use Doctrine\Common\Annotations;
use Drest\Configuration;
use Drest\DrestException;
use Drest\Mapping\Annotation;
use Drest\Mapping;
use Drest\Mapping\RouteMetaData;

/**
 * The JsonDriver reads a configuration file (config.json) rather than utilizing annotations.
 */
class JsonDriver extends PhpDriver
{

    public function __construct()
    {
        parent::__construct($paths);
        $json = null;
        $filename = self::$configuration_filepath . DIRECTORY_SEPARATOR . self::$configuration_filename;
        
        if(!file_exists($filename)) { 
            throw new \RuntimeException('The configuration file does not exist at this path: ' . $filename);
        }

        $json = json_decode(file_get_contents($filename), true);

        if($json == null) {
            throw new \RuntimeException('The configuration file does not have valid JSON: ' . $filename);
        }

        $entities = [];
        foreach($json['resources'] as $resource) {
            $tmp = $resource;
            $entity = $resource['entity'];
            unset($tmp['entity']);
            $entities[$entity] = $tmp;
        }
        $this->classes = $entities;
    }
}
