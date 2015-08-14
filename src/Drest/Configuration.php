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
namespace Drest;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\Mapping\ClassMetadataInfo as ORMClassMetaDataInfo;
use DrestCommon\Request\Request;
use DrestCommon\Response\Response;

class Configuration
{
    const DETECT_CONTENT_HEADER = 1;
    const DETECT_CONTENT_EXTENSION = 2;
    const DETECT_CONTENT_PARAM = 3;
    const EXPOSE_REQUEST_HEADER = 1;
    const EXPOSE_REQUEST_PARAM = 2;
    const EXPOSE_REQUEST_PARAM_GET = 3;
    const EXPOSE_REQUEST_PARAM_POST = 4;

    // any alteration will have an effect on drest-common (AbstractRepresentation.php)
    public static $detectContentOptions = [
        self::DETECT_CONTENT_HEADER => 'Header',
        self::DETECT_CONTENT_EXTENSION => 'Extension',
        self::DETECT_CONTENT_PARAM => 'Parameter'
    ];
    public static $exposeRequestOptions = [
        self::EXPOSE_REQUEST_HEADER => 'X-Expose',
        self::EXPOSE_REQUEST_PARAM => 'Parameter',
        self::EXPOSE_REQUEST_PARAM_GET => 'Get Parameter',
        self::EXPOSE_REQUEST_PARAM_POST => 'Post Parameter'
    ];
    /**
     * Configuration attributes
     * @var array
     */
    protected $_attributes = [];

    /**
     * Set configuration defaults
     */
    public function __construct()
    {
        // Turn off debug mode
        $this->setDebugMode(false);
        // Allow content detection using the Accept header
        $this->setDetectContentOptions([self::DETECT_CONTENT_HEADER => 'Accept']);
        // Use Json and XML as the default representations
        // @todo: This probably should be registered in this way. Use a similar method as the adapter classes
        $this->setDefaultRepresentations(array('Json', 'Xml'));
        // Set the default method for retreiving class metadata.
        $this->setMetadataDriverClass('\Drest\Mapping\Driver\AnnotationDriver');
        // register the default request adapter classes
        $this->_attributes['requestAdapterClasses'] = [];
        $this->registerRequestAdapterClasses(Request::$defaultAdapterClasses);
        // register the default response adapter classes
        $this->_attributes['responseAdapterClasses'] = [];
        $this->registerResponseAdapterClasses(Response::$defaultAdapterClasses);
        // Depth of exposure on entity fields => relations
        $this->setExposureDepth(2);
        // Don't follow any relation type
        $this->setExposureRelationsFetchType(null);
        // Don't set any expose request options
        $this->setExposeRequestOptions([]);
        // Allow OPTIONS request on resources
        $this->setAllowOptionsRequest(true);
        // Set the route base paths to be an empty array
        $this->_attributes['routeBasePaths'] = [];
        // Set the paths to the config files as an empty array
        $this->_attributes['pathsToConfigFiles'] = [];
        // Don't send a 415 if we don't match a representation class, default to first available one
        $this->set415ForNoMediaMatch(false);
        // Set the default error handler class (immutable)
        $this->_attributes['defaultErrorHandlerClass'] = 'DrestCommon\\Error\\Handler\\DefaultHandler';
    }

    /**
     * Set an attribute for the driver configuration 
     * @param string $attribute, string $value
     */
    public function setAttribute($attribute, $value) {
        $this->_attributes[$attribute] = $value;
    }

    /**
     * Get an attribute from the driver configuration
     * @param string $attribute
     * @return string $this->_attributes[$attribute]
     */
    public function getAttribute($attribute) {
        if(isset($this->_attributes[$attribute])) {
            return $this->_attributes[$attribute];
        }
        return null;        
    }

    /**
     * Set the debug mode - when on all DrestExceptions are rethrown,
     * otherwise 500 errors are returned from the REST service
     * Should be switched off in production
     * @param boolean $setting
     */
    public function setDebugMode($setting)
    {
        $this->_attributes['debugMode'] = (bool) $setting;
    }

    /**
     * Set the default representation classes to be used across the entire API.
     * Any representations defined locally on a resource will take precedence
     * @param string[] $representations
     */
    public function setDefaultRepresentations(array $representations)
    {
        $this->_attributes['defaultRepresentations'] = $representations;
    }

    /**
     * Sets the class name of the metadata driver for instantiation.
     *
     * @param string $driver
     */
    public function setMetadataDriverClass($driver) {
        $this->_attributes['metaDataDriver'] = $driver;
    }

    /**
     * Returns the class name of the metadata driver.
     * @return string The namespaced class name.
     */
    public function getMetadataDriverClass() {
        return $this->_attributes['metaDataDriver'];
    }


    /**
     * Register an array of request adapter classes
     * @param array $classes
     */
    public function registerRequestAdapterClasses(array $classes)
    {
        foreach ($classes as $class) {
            $this->registerRequestAdapterClass($class);
        }
    }

    /**
     * Register a class name to be used as a request adapter
     * @param string $class
     */
    public function registerRequestAdapterClass($class)
    {
        if ($this->containsRequestAdapterClass($class) === false) {
            $this->_attributes['requestAdapterClasses'][] = $class;
        }
    }

    /**
     * Does this configuration contain a request adapter class by name
     * @param  string          $className
     * @return boolean|integer returns the offset position if it exists (can be zero, do type check)
     */
    public function containsRequestAdapterClass($className)
    {
        return $this->searchForAdapterClass($className, 'requestAdapterClasses');
    }

    /**
     * Search for a request/response adapter class by name
     * @param $className
     * @param $adapterType
     * @return bool|integer returns the offset position if it exists
     */
    protected function searchForAdapterClass($className, $adapterType)
    {
        if (($offset = array_search($className, $this->_attributes[$adapterType])) !== false) {
            return $offset;
        }
        return false;
    }

    /**
     * Register an array of response adapter classes
     * @param array $classes
     */
    public function registerResponseAdapterClasses(array $classes)
    {
        foreach ($classes as $class) {
            $this->registerResponseAdapterClass($class);
        }
    }

    /**
     * Register a class name to be used as a response adapter
     * @param string $class
     */
    public function registerResponseAdapterClass($class)
    {
        if ($this->containsResponseAdapterClass($class) === false) {
            $this->_attributes['responseAdapterClasses'][] = $class;
        }
    }

    /**
     * Does this configuration contain a response adapter class by name
     * @param  string          $className
     * @return boolean|integer returns the offset position if it exists (can be zero, do type check)
     */
    public function containsResponseAdapterClass($className)
    {
        return $this->searchForAdapterClass($className, 'responseAdapterClasses');
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
     * Set the exposure fields by following relations that have the a certain fetch type.
     * This is useful if you only want to display fields that are loaded eagerly.
     * eg ->setExposureRelationsFetchType(ORMClassMetaDataInfo::FETCH_EAGER)
     * @param  integer        $fetch
     * @throws DrestException
     */
    public function setExposureRelationsFetchType($fetch)
    {
        switch ($fetch) {
            case ORMClassMetaDataInfo::FETCH_EAGER:
            case ORMClassMetaDataInfo::FETCH_LAZY:
            case ORMClassMetaDataInfo::FETCH_EXTRA_LAZY:
            case null:
                $this->_attributes['defaultExposureRelationsFetchType'] = $fetch;
                break;
            default:
                throw DrestException::invalidExposeRelationFetchType();
        }
    }

    /**
     * A setting to generically allow OPTIONS requests across the entire API.
     * This can be overridden by using the @Route\Metadata $allowOptions parameter
     * @param boolean $value
     */
    public function setAllowOptionsRequest($value)
    {
        $this->_attributes['allowOptionsRequest'] = (bool) $value;
    }

    /**
     * When no content type is detected, the response will default to using the first available.
     * To switch this feature off and send a 415 error, call this configuration function
     * @param boolean $value
     */
    public function set415ForNoMediaMatch($value = true)
    {
        $this->_attributes['send415ForNoMediaMatch'] = (bool) $value;
    }

    /**
     * Sets the cache driver implementation that is used for metadata caching.
     *
     * @param \Doctrine\Common\Cache\Cache $cacheImpl
     */
    public function setMetadataCacheImpl(Cache $cacheImpl)
    {
        $this->_attributes['metadataCacheImpl'] = $cacheImpl;
    }

    /**
     * Set a content option for detecting the media type to be used. To unset pass null as a value
     * For any options that don't required a value, set them to true to activate them
     * @param  integer        $option
     * @param  string         $value
     * @throws DrestException
     */
    public function setDetectContentOption($option, $value)
    {
        if (array_key_exists($option, self::$detectContentOptions)) {
            $this->_attributes['detectContentOptions'][$option] = $value;
        } else {
            throw DrestException::unknownDetectContentOption();
        }
    }

    /**
     * Get detect content options. Returns an array indexed using constants as array key
     * (value will be the value to be used for the content options)
     * Eg array(self::DETECT_CONTENT_HEADER => 'Accept')
     * @return array
     */
    public function getDetectContentOptions()
    {
        return $this->_attributes['detectContentOptions'];
    }

    /**
     * Set the methods to be used for detecting content type to be used to pull requests, overwrites previous settings
     * Eg ->setDetectContentOptions(array(self::DETECT_CONTENT_HEADER => $headerName))
     * self::DETECT_CONTENT_HEADER           = Uses the a header to detect the required content (typically use Accept)
     * self::DETECT_CONTENT_EXTENSION        = Uses an extension on the url eg .xml
     * self::DETECT_CONTENT_PARAM            = Uses a the "format" parameter
     * @param array - pass either a single array value using the constant value as a key, or a multi-dimensional array.
     */
    public function setDetectContentOptions(array $options)
    {
        $this->_attributes['detectContentOptions'] = [];
        foreach ($options as $key => $value) {
            $this->setDetectContentOption($key, $value);
        }
    }

    /**
     * Get the 415 for no representation match setting
     * @return boolean
     */
    public function get415ForNoMediaMatchSetting()
    {
        return $this->_attributes['send415ForNoMediaMatch'];
    }

    /**
     * Method used to retrieve the required expose contents from the client. To unset pass null as value
     * @param  integer        $option
     * @param  string         $value
     * @throws DrestException
     */
    public function setExposeRequestOption($option, $value)
    {
        if (array_key_exists($option, self::$exposeRequestOptions)) {
            $this->_attributes['exposeRequestOptions'][$option] = $value;
        } else {
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
     * Set the methods to be used for detecting the expose content from the client. Overwrites any previous value
     * Eg ->setExposeRequestOptions(array(self::EXPOSE_REQUEST_HEADER => $headerName))
     * @param array $options
     */
    public function setExposeRequestOptions(array $options)
    {
        $this->_attributes['exposeRequestOptions'] = [];
        foreach ($options as $key => $value) {
            $this->setExposeRequestOption($key, $value);
        }
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
     * Gets the configured expose relations fetch type - returns null if not set
     * @return integer|null $result
     */
    public function getExposureRelationsFetchType()
    {
        if (isset($this->_attributes['defaultExposureRelationsFetchType'])) {
            return $this->_attributes['defaultExposureRelationsFetchType'];
        }

        return null;
    }

    /**
     * Un-register an adapter class name entry
     * @param string $class
     */
    public function unregisterResponseAdapterClass($class)
    {
        if (($offset = $this->containsResponseAdapterClass($class)) !== false) {
            unset($this->_attributes['responseAdapterClasses'][$offset]);
        }
    }

    /**
     * get the registered response adapted classes
     * @return array $class_name - a string array of class names
     */
    public function getRegisteredResponseAdapterClasses()
    {
        return $this->_attributes['responseAdapterClasses'];
    }

    /**
     * Un-register an adapter class name entry
     * @param string $class
     */
    public function unregisterRequestAdapterClass($class)
    {
        if (($offset = $this->containsRequestAdapterClass($class)) !== false) {
            unset($this->_attributes['requestAdapterClasses'][$offset]);
        }
    }

    /**
     * get the registered request adapted classes
     * @return array $class_name - a string array of class names
     */
    public function getRegisteredRequestAdapterClasses()
    {
        return $this->_attributes['requestAdapterClasses'];
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
     * @param array $paths
     */
    public function addPathsToConfigFiles($paths = [])
    {
        if (!isset($this->_attributes['pathsToConfigFiles'])) {
            $this->_attributes['pathsToConfigFiles'] = [];
        }
        $this->_attributes['pathsToConfigFiles'] = array_merge($this->_attributes['pathsToConfigFiles'], (array) $paths);
    }

    /**
     * Remove all the registered paths to config files, or just a specific entry $path
     * @param string $path
     */
    public function removePathsToConfigFiles($path = null)
    {
        if (is_null($path)) {
            $this->_attributes['pathsToConfigFiles'] = [];
        } else {
            if (($offset = array_search($path, $this->_attributes['pathsToConfigFiles'])) !== false)
            {
                unset($this->_attributes['pathsToConfigFiles'][$offset]);
            }
        }
    }

    /**
     * Get the paths to the drest configuration files
     * @return array $paths
     */
    public function getPathsToConfigFiles()
    {
        return $this->_attributes['pathsToConfigFiles'];
    }

    /**
     * Add a base path to be used when matching routes. Eg /v1 would be useful IF you want versioning in the URL
     * @param  string         $basePath
     * @throws DrestException
     */
    public function addRouteBasePath($basePath)
    {
        if (!is_string($basePath)) {
            throw DrestException::basePathMustBeAString();
        }
        $this->_attributes['routeBasePaths'][] = trim($basePath, '/');
    }

    /**
     * Remove a route base path (if it has been registered)
     * @param  string  $basePath
     * @return boolean true if $basePath was unset
     */
    public function removeRouteBasePath($basePath)
    {
        $basePath = trim($basePath, '/');
        if (!is_string($basePath)) {
            return false;
        }
        if (($offset = array_search($basePath, $this->_attributes['routeBasePaths'])) !== false) {
            unset($this->_attributes['routeBasePaths'][$offset]);

            return true;
        }

        return false;
    }

    /**
     * Have base paths been registered - or look for a specific entry
     * @param  string  $basePath - optional, has a specific route path been registered
     * @return boolean true if route base paths have been registered
     */
    public function hasRouteBasePaths($basePath = null)
    {
        if (!is_null($basePath)) {
            $basePath = trim($basePath, '/');

            return in_array($basePath, $this->_attributes['routeBasePaths']);
        }

        return (sizeof($this->_attributes['routeBasePaths']) > 0) ? true : false;
    }

    /**
     * Get all registered base path or a specific entry
     * @return array $basePaths
     */
    public function getRouteBasePaths()
    {
        return (array) $this->_attributes['routeBasePaths'];
    }

    /**
     * Get the default representation classes to be used across the entire API
     * @return array representation classes
     */
    public function getDefaultRepresentations()
    {
        return (array) $this->_attributes['defaultRepresentations'];
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
     * @throws DrestException If a configuration setting has a value that is not suitable for a production.
     */
    public function ensureProductionSettings()
    {
        if ($this->inDebugMode()) {
            throw DrestException::currentlyRunningDebugMode();
        }

        if (!$this->getMetadataCacheImpl()) {
            throw DrestException::metadataCacheNotConfigured();
        }
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
}
