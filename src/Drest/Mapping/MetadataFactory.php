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
namespace Drest\Mapping;

use Doctrine\Common\Cache\Cache;
use Drest\DrestException;
use Drest\Mapping\Driver\DriverInterface;

class MetadataFactory
{

    /**
     * Driver attached to this meta data factory
     * @var \Drest\Mapping\Driver\DriverInterface $driver
     */
    private $driver;

    /**
     * Cache used to prevent metadata reloading
     * @var \Doctrine\Common\Cache\Cache $cache
     */
    private $cache;

    /**
     * A prefix string to use when interacting with a Doctrine cache object
     * @var string $cache_prefix
     */
    private $cache_prefix = '_drest_';

    /**
     * Metadata that has already been loaded by the driver
     * @var array $loadedMetadata
     */
    private $loadedMetadata = array();

    /**
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Set the doctrine cache drive to be user
     * @param Cache $cache
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get all class names
     * @return array class names registered to this driver
     */
    public function getAllClassNames()
    {
        return $this->driver->getAllClassNames();
    }

    /**
     * Get metadata for a certain class - loads once and caches
     * @param  string                $className
     * @throws \Drest\DrestException
     * @return ClassMetaData         $metaData
     */
    public function getMetadataForClass($className)
    {
        if (isset($this->loadedMetadata[$className])) {
            return $this->loadedMetadata[$className];
        }

        // check the cache
        if ($this->cache !== null) {
            $classMetadata = $this->cache->fetch($this->cache_prefix . $className);
            if ($classMetadata instanceof ClassMetaData) {
                if ($classMetadata->expired()) {
                    $this->cache->delete($this->cache_prefix . $className);
                } else {
                    $this->loadedMetadata[$className] = $classMetadata;

                    return $classMetadata;
                }
            }
        }

        $classMetadata = $this->driver->loadMetadataForClass($className);
        if ($classMetadata !== null) {
            $this->loadedMetadata[$className] = $classMetadata;
            if ($this->cache !== null) {
                $this->cache->save($this->cache_prefix . $className, $classMetadata);
            }

            return $classMetadata;
        }

        if (is_null($this->loadedMetadata[$className])) {
            throw DrestException::unableToLoadMetaDataFromDriver();
        }

        return $this->loadedMetadata[$className];
    }
}
