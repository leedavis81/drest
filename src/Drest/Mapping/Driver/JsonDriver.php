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
    protected $paths = [];

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

            foreach ($this->paths as $path)
            {
                if(!file_exists($path)) {
                    throw DriverException::configurationFileDoesntExist($path);
                }

                $resources = json_decode(file_get_contents($path), true);

                if($resources === null) {
                    throw DriverException::configurationFileIsInvalid('Json');
                }

                $entities = [];
                foreach($resources['resources'] as $resource) {
                    $entity = $resource['entity'];
                    $entities[$entity] = $resource;
                    unset($entities[$entity]['entity']);
                }

                $this->classes = array_merge($this->classes, $entities);
            }
        }

        return array_keys($this->classes);
    }

}