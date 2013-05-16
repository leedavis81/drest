<?php

namespace Drest;

use Drest\DrestException,
	Drest\Mapping\Driver\AnnotationsDriver,
	Doctrine\Common\Cache\Cache,
    Doctrine\Common\Annotations\AnnotationRegistry,

    Doctrine\ORM\Mapping\ClassMetadataInfo as ORMClassMetaDataInfo;


class Configuration
{

    const DETECT_CONTENT_HEADER = 1;
    const DETECT_CONTENT_EXTENSION = 2;
    const DETECT_CONTENT_PARAM = 3;

    const EXPOSE_REQUEST_HEADER = 1;
    const EXPOSE_REQUEST_PARAM = 2;
    const EXPOSE_REQUEST_PARAM_GET = 3;
    const EXPOSE_REQUEST_PARAM_POST = 4;

    public static $detectContentOptions = array(
        self::DETECT_CONTENT_HEADER => 'Accept Header',
        self::DETECT_CONTENT_EXTENSION => 'Extension',
        self::DETECT_CONTENT_PARAM => 'Parameter'
    );

    public static $exposeRequestOptions = array(
        self::EXPOSE_REQUEST_HEADER => 'X-Expose',
        self::EXPOSE_REQUEST_PARAM => 'Parameter',
        self::EXPOSE_REQUEST_PARAM_GET => 'Get Parameter',
        self::EXPOSE_REQUEST_PARAM_POST => 'Post Parameter'
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
        // Turn off debug mode
        $this->setDebugMode(false);
        // Allow content detection using the Accept header
        $this->setDetectContentOptions(array(
            self::DETECT_CONTENT_HEADER => 'Accept'
        ));
        // Use Json and XML as the default writers
        $this->setDefaultWriters(array('Json', 'Xml'));
        // Default service class to be used
        $this->setDefaultServiceClass('Drest\Service\DefaultService');
        // Depth of exposure on entity fields => relations
        $this->setExposureDepth(2);
        // Dont follow any relation type
        $this->setExposureRelationsFetchType(null);
        // Don't set any expose request options
        $this->setExposeRequestOptions(array());
        // Allow OPTIONS request on resources
        $this->setAllowOptionsRequest(true);
        // Set the route base paths to be an empty array
        $this->_attributes['routeBasePaths'] = array();
        // Dont send a 415 if we dont match a writer, default to first available writer
        $this->set415ForNoWriterMatch(false);
        // Set the default error handler class (immutable)
        $this->_attributes['defaultErrorHandlerClass'] = 'Drest\\ErrorHandler\\DefaultHandler';
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
     * Eg ->setDetectContentOptions(array(self::DETECT_CONTENT_HEADER => $headerName))
     * self::DETECT_CONTENT_HEADER 			= Uses the a header to detect the required content (typically use Accept)
     * self::DETECT_CONTENT_EXTENSION 		= Uses an extension on the url eg .xml
     * self::DETECT_CONTENT_PARAM 			= Uses a the "format" parameter
     * @param array $values pass in either a single array value using the constant value as a key, or a multi-dimensional array.
     */
    public function setDetectContentOptions(array $options)
    {
        $this->_attributes['detectContentOptions'] = array();
        foreach ($options as $key => $value)
        {
            $this->setDetectContentOption($key, $value);
        }
    }

    /**
     * Set a content option for detecting the media type to be used. To unset pass null as a value
     * For any options that don't required a value, set them to true to activate them
     * @param integer $option
     * @param string $value
     */
    public function setDetectContentOption($option, $value)
    {
        if (array_key_exists($option, self::$detectContentOptions))
        {
            $this->_attributes['detectContentOptions'][$option] = $value;
        } else
        {
            throw DrestException::unknownDetectContentOption();
        }
    }

    /**
     * Get detect content options. Returns an array indexed using constants as array key (value will be the value to be used for the content options)
     * Eg array(self::DETECT_CONTENT_HEADER => 'Accept')
     * @return array
     */
    public function getDetectContentOptions()
    {
        return $this->_attributes['detectContentOptions'];
    }

    /**
     *
     * When no content type is detected, the response will default to using the first available.
     * To switch this feature off and send a 415 error, call this configuration function
     * @param boolean $value
     */
    public function set415ForNoWriterMatch($value = true)
    {
        $this->_attributes['send415ForNoWriterMatch'] = $value;
    }

    /**
     * Get the 415 for no writer match setting
     * @return boolean $value
     */
    public function get415ForNoWriterMatchSetting()
    {
        return $this->_attributes['send415ForNoWriterMatch'];
    }

    /**
     * Set the methods to be used for detecting the expose content from the client. Overwrites any previous value
     * Eg ->setExposeRequestOptions(array(self::EXPOSE_REQUEST_HEADER => $headerName))
     * @param array $options
     */
    public function setExposeRequestOptions(array $options)
    {
        $this->_attributes['exposeRequestOptions'] = array();
        foreach ($options as $key => $value)
        {
            $this->setExposeRequestOption($key, $value);
        }
    }

    /**
     * Method used to retreive the required expose contents from the client. To unset pass null as value
     * @param integer $option
     * @param string $value
     */
    public function setExposeRequestOption($option, $value)
    {
        if (array_key_exists($option, self::$exposeRequestOptions))
        {
            $this->_attributes['exposeRequestOptions'][$option] = $value;
        } else
        {
            throw DrestException::unknownExposeRequestOption();
        }
    }

    /**
     * Get the expose request options
     * @return array $options
     */
    public function getExposeRequestOptions()
    {
        return $this->_attributes['exposeRequestOptions'];
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
     * Set the default depth of columns to expose to client
     * @param integer $depth
     */
    public function setExposureDepth($depth)
    {
        $this->_attributes['defaultExposureDepth'] = (int) $depth;
    }

    /**
     * Get the default exposure depth
     * @return integer $depth
     */
    public function getExposureDepth()
    {
        return (int) $this->_attributes['defaultExposureDepth'];
    }

    /**
     * Set the exposure fields by following relations that have the a certain fetch type.
     * This is useful if you only want to display fields that are loaded eagerly.
     * eg ->setExposureRelationsFetchType(ORMClassMetaDataInfo::FETCH_EAGER)
     * @param integer $fetch
     */
    public function setExposureRelationsFetchType($fetch)
    {
        switch ($fetch)
        {
            case ORMClassMetaDataInfo::FETCH_EAGER:
            case ORMClassMetaDataInfo::FETCH_LAZY:
            case ORMClassMetaDataInfo::FETCH_EXTRA_LAZY:
            case null:
                $this->_attributes['defaultExposureRelationsFetchType'] = $fetch;
                break;
            default:
                throw DrestException::invalidExposeRelationFetchType();
                break;
        }
    }

    /**
     * Gets the configured expose relations fetch type - returns null if not set
     * @return integer|null $result
     */
    public function getExposureRelationsFetchType()
    {
        if (isset($this->_attributes['defaultExposureRelationsFetchType']))
        {
            return $this->_attributes['defaultExposureRelationsFetchType'];
        }
    }

    /**
     * A setting to generically allow OPTIONS requests across the entire API.
     * This can be overriden by using the @Route\Metadata $allowOptions parameter
     * @param boolean $value
     */
    public function setAllowOptionsRequest($value)
    {
        $this->_attributes['allowOptionsRequest'] = (bool) $value;
    }

    /**
     * Are we globally allowing OPTIONS requests across all routes
     * @return boolean $value
     */
    public function getAllowOptionsRequest()
    {
        return $this->_attributes['allowOptionsRequest'];
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
     * Add a base path to be used when matching routes. Eg /v1 would be useful IF you want versioning in the URL
     * @param string $basePath
     */
    public function addRouteBasePath($basePath)
    {
        if (!is_string($basePath))
        {
            throw DrestException::basePathMustBeAString();
        }
        $this->_attributes['routeBasePaths'][] = trim($basePath, '/');
    }

    /**
     * Remove a route base path (if it has been registered)
     * @param string $basePath
     * @return boolean true if $basePath was unset
     */
    public function removeRouteBasePath($basePath)
    {
        $basePath = trim($basePath, '/');
        if (!is_string($basePath))
        {
            return false;
        }
        if (($offset = array_search($basePath, $this->_attributes['routeBasePaths'])) !== false)
        {
            unset($this->_attributes['routeBasePaths'][$offset]);
            return true;
        }
        return false;
    }

    /**
     * Have base paths been registered - or look for a specific entry
     * @param string $basePath - optional, has a specific route path been registered
     * @return boolean true if route base paths have been registered
     */
    public function hasRouteBasePaths($basePath = null)
    {
        if (!is_null($basePath))
        {
            $basePath = trim($basePath, '/');
            return in_array($basePath, $this->_attributes['routeBasePaths']);
        }
        return (sizeof($this->_attributes['routeBasePaths']) > 0) ? true : false;
    }

    /**
     * Get all registered base path or a specific entry
     * @return array $basePaths
     */
    public function getRouteBasePaths($basePath = null)
    {
        return $this->_attributes['routeBasePaths'];
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
     * Get the default error handler class
     * @return string $className
     */
    public function getDefaultErrorHandlerClass()
    {
        return $this->_attributes['defaultErrorHandlerClass'];
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