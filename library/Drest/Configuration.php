<?php

namespace Drest;

use Drest\DrestException,
	Drest\Mapping\Driver\AnnotationsDriver,
	Doctrine\Common\Cache\Cache,
    Doctrine\Common\Annotations\AnnotationRegistry;


class Configuration
{

    const DETECT_CONTENT_ACCEPT_HEADER = 1;
    const DETECT_CONTENT_EXTENSION = 2;
    const DETECT_CONTENT_PARAM = 3;

    public static $detectContentOptions = array(
        self::DETECT_CONTENT_ACCEPT_HEADER => 'Accept Header',
        self::DETECT_CONTENT_EXTENSION => 'Extension',
        self::DETECT_CONTENT_PARAM => 'Parameter'
    );

    /**
     * Configuration attributes
     * @var array
     */
    protected $_attributes = array();


    /**
     * Set configuration defaults
     */
    public function __construct()
    {
        // By default only allow the Accept header detection
        $this->setDetectContentOptions(array(self::DETECT_CONTENT_ACCEPT_HEADER, self::DETECT_CONTENT_EXTENSION, self::DETECT_CONTENT_PARAM));
    }


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
     * Set the methods to be used for detecting content type to be used, overwrites previous settings
     * self::DETECT_CONTENT_ACCEPT_HEADER 	= Uses the accept header to detect the required content
     * self::DETECT_CONTENT_EXTENSION 		= Uses an extension on the url eg .xml
     * self::DETECT_CONTENT_PARAM 			= Uses a the "format" parameter
     * @param mixed $values pass in either a single constant value, or an array of them.
     */
    public function setDetectContentOptions($values)
    {
        $values = (array) $values;
        $this->_attributes['detectContentOptions'] = array();
        foreach ($values as $value)
        {
            if (array_key_exists($value, self::$detectContentOptions))
            {
                $this->_attributes['detectContentOptions'][] = $value;
            }
        }
    }

    /**
     * detect content options
     * @return array
     */
    public function getDetectContentOptions()
    {
        return $this->_attributes['detectContentOptions'];
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