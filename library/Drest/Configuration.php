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
        $this->setDetectContentOptions(array(self::DETECT_CONTENT_ACCEPT_HEADER));
        $this->setDefaultWriters(array('Json', 'Xml'));

        $this->setDebugMode(false);
        $this->setDefaultServiceClass('Drest\Service\DefaultService');
    }


    /**
     * Set the debug mode - when on all DrestExceptions are rethrown, otherwise 500 errors are returned from the REST service
     * Should be switched off in production
     * @param boolean $setting
     */
    public function setDebugMode($setting)
    {
        $this->_attributes['debugMode'] = (bool) $setting;
    }

    /**
     * Are we in debug mode?
     * @return boolean
     */
    public function inDebugMode()
    {
        return $this->_attributes['debugMode'];
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
        if (!$cacheImpl instanceof \Doctrine\Common\Cache\Cache)
        {
            throw DrestException::invalidCacheInstance();
            $this->_attributes['metadataCacheImpl'] = $cacheImpl;
        }

        $this->_attributes['metadataCacheImpl'] = $cacheImpl;
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
     * Set the default service class to be used
     * @param string $className
     */
    public function setDefaultServiceClass($className)
    {
        $this->_attributes['defaultServiceClass'] = $className;
    }

    /**
     * Get the default service class
     * @return string $className
     */
    public function getDefaultServiceClass()
    {
        return $this->_attributes['defaultServiceClass'];
    }

    /**
     * Register paths to your configuration files. This will typically be where your entities live
     * This will overwrite any previously registered paths. To add new one use addPathsToConfigFiles($paths)
     */
    public function addPathsToConfigFiles($paths = array())
    {
        if (!isset($this->_attributes['pathsToConfigFiles']))
        {
            $this->_attributes['pathsToConfigFiles'] = array();
        }
        $this->_attributes['pathsToConfigFiles'] = array_merge($this->_attributes['pathsToConfigFiles'], (array) $paths);
    }

    /**
     * Remove all the registered paths to config files, or just a specific entry $path
     * @param string $path
     */
    public function removePathsToConfigFiles($path = null)
    {
        if (is_null($path))
        {
            $this->_attributes['pathsToConfigFiles'] = array();
        } else
        {
            $offset = array_search($path, $this->_attributes['pathsToConfigFiles']);

            if ($offset !== false)
            {
                unset($this->_attributes['pathsToConfigFiles'][$offset]);
            }
        }
    }

    /**
     * Get the paths to the drest configutation files
     * @return array $paths
     */
    public function getPathsToConfigFiles()
    {
        return $this->_attributes['pathsToConfigFiles'];
    }

    /**
     * Get the default writer classes to be used across the entire API
     * @return array writer classes
     */
    public function getDefaultWriters()
    {
        return $this->_attributes['defaultWriters'];
    }

    /**
     * Set the default writers to be used across the entire API. Any writers defined locally on a resource will take presedence
     * @param array $writers
     */
    public function setDefaultWriters(array $writers)
    {
        $this->_attributes['defaultWriters'] = $writers;
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
        if ($this->inDebugMode())
        {
            throw DrestException::currentlyRunningDebugMode();
        }

        if ( ! $this->getMetadataCacheImpl()) {
            throw DrestException::metadataCacheNotConfigured();
        }
    }


}