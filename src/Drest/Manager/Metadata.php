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

use Doctrine\Common\Annotations\AnnotationReader;
use Drest\Mapping\MetadataFactory;
use Drest\Mapping\Driver\AnnotationDriver;
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
     * Annotation Driver being used
     * @var AnnotationDriver $metaDataDriver
     */
    protected $metaDataDriver;

    /**
     * Create a metadata manager instance
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $this->metaDataDriver = AnnotationDriver::create(
            new AnnotationReader(),
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
        foreach ($this->getAllClassNames() as $class) {
            $classMetaData = $this->getMetaDataForClass($class);
            $router->registerRoutes($classMetaData->getRoutesMetaData());
        }
    }
}