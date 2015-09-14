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
namespace Drest\Manager;

use Drest\DrestException;
use Drest\Mapping\MetadataFactory;
use Drest\Configuration;
use Drest\Router;

class Metadata
{

    /**
     * Metadata factory object
     * @var MetadataFactory $metadataFactory
     */
    protected $metadataFactory;

    /**
     * Mapping Driver being used
     * @var \Drest\Mapping\Driver\DriverInterface $metaDataDriver
     */
    protected $metaDataDriver;

    /**
     * Create a metadata manager instance
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $driver = $config->getMetadataDriverClass();

        $this->metaDataDriver = $driver::create(
            $config->getPathsToConfigFiles()
        );

        $this->metadataFactory = new MetadataFactory(
            $this->metaDataDriver
        );

        if ($cache = $config->getMetadataCacheImpl()) {
            $this->metadataFactory->setCache($cache);
        }
    }

    /**
     * Static call to create a representation instance
     * @param Configuration $config
     * @return MetaData
     */
    public static function create(Configuration &$config)
    {
        return new self($config);
    }


    /**
     * Get all the class names
     * @return array
     */
    public function getAllClassNames()
    {
        return $this->metadataFactory->getAllClassNames();
    }

    /**
     * Get the metadata for a class
     * @param $class
     * @throws \Drest\DrestException
     * @return \Drest\Mapping\ClassMetaData
     */
    public function getMetaDataForClass($class)
    {
        return $this->metadataFactory->getMetadataForClass($class);
    }

    /**
     * Read any defined route patterns from metadata and inject them into the router
     * @param Router $router
     */
    public function registerRoutes(Router $router)
    {
        foreach ($this->metaDataDriver->getAllClassNames() as $class)
        {
            $classMetaData = $this->getMetaDataForClass($class);
            $router->registerRoutes($classMetaData->getRoutesMetaData());
        }
    }
}
