<?php

namespace Drest;

use Drest\DrestException,
	Drest\Mapping\Driver\AnnotationsDriver,
	Doctrine\Common\Cache\Cache,
    Doctrine\Common\Annotations\AnnotationRegistry;


class Configuration
{
    /**
     * Configuration attributes
     *
     * @var array
     */
    protected $_attributes = array();


    /**
     * Gets the cache driver implementation that is used for metadata caching.
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getMetadataCacheImpl()
    {
        return isset($this->_attributes['metadataCacheImpl'])
            ? $this->_attributes['metadataCacheImpl']
            : null;
    }

    /**
     * Sets the cache driver implementation that is used for metadata caching.
     *
     * @param \Doctrine\Common\Cache\Cache $cacheImpl
     */
    public function setMetadataCacheImpl(Cache $cacheImpl)
    {
        if ($cacheImpl instanceof \Doctrine\Common\Cache\Cache)
        {
            $this->_attributes['metadataCacheImpl'] = new \Metadata\Cache\DoctrineCacheAdapter('_drest_', $cacheImpl);
        } else
        {
            $this->_attributes['metadataCacheImpl'] = $cacheImpl;
        }
    }


    /**
     * Ensures that this Configuration instance contains settings that are
     * suitable for a production environment.
     *
     * @throws DrestException If a configuration setting has a value that is not
     *                      suitable for a production environment.
     */
    public function ensureProductionSettings()
    {
        if ( ! $this->getMetadataCacheImpl()) {
            throw DrestException::metadataCacheNotConfigured();
        }
    }


}