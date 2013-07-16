<?php
namespace Drest;

use Drest\Mapping;
use Exception;

/**
 * Base exception class for all Drest exceptions.
 *
 * @author Lee
 */
class DrestException extends Exception
{

    // Set up and configuration
    public static function metadataCacheNotConfigured()
    {
        return new self('Class Metadata Cache is not configured, ensure an instance of Doctrine\Common\Cache\Cache is passed to the Drest\Configuration::setMetadataCacheImpl()');
    }

    public static function invalidCacheInstance()
    {
        return new self('Cache must be an instance of Doctrine\Common\Cache\Cache');
    }

    public static function currentlyRunningDebugMode()
    {
        return new self('Debug mode is set to on. This will cause configuration exceptions to be displayed and should be switched off in production');
    }

    public static function missingMappingDriverImpl()
    {
        return new self('It\'s a requirement to specify a Metadata Driver and pass it to Drest\\Configuration::setMetadataDriverImpl().');
    }

    public static function annotatedResourceRequiresAtLeastOneServiceDefinition($className)
    {
        return new self('The annotated resource on class ' . $className . ' doesn\'t have any service definitions. Ensure you have "services={@Drest\Service(..)} set');
    }

    public static function routeAlreadyDefinedWithName($class, $name)
    {
        return new self('Route on class ' . $class . ' already exists with the name ' . $name . '. These must be unique');
    }

    public static function routeNameIsEmpty()
    {
        return new self('Route name used cannot be blank, and must only contain alphanumerics or underscore');
    }

    public static function invalidHttpVerbUsed($verb)
    {
        return new self('Used an unknown HTTP verb of "' . $verb . '"');
    }

    public static function unknownContentOption($type)
    {
        return new self('Used an unknown content type of "' . $type . '". values ELEMENT or COLLECTION should be used.');
    }

    public static function unknownDetectContentOption()
    {
        return new self('Content option used is invalid. Please see DETECT_CONTENT_* options in Drest\Configuration');
    }

    public static function pathToConfigFilesRequired()
    {
        return new self('Path to your configuration files are required for the driver to retrieve all class names');
    }

    public static function pathToConfigFilesMustBeDirectory($path)
    {
        return new self('The path to your configuration files must be a directory. "' . $path . '" given.');
    }

    public static function unableToLoadMetaDataFromDriver()
    {
        return new self('Unable to load metadata using supplied driver');
    }

    public static function invalidExposeRelationFetchType()
    {
        return new self('Invalid relation fetch type used. Please see Doctrine\ORM\Mapping\ClassMetadataInfo::FETCH_* for avaiable options');
    }

    public static function unknownExposeRequestOption()
    {
        return new self('Unknown expose request option used. Please see EXPOSE_REQUEST_* options in Drest\Configuration');
    }

    public static function unableToParseExposeFieldsString()
    {
        return new self('Unable to parse expose fields string. Must contain required field names to be pipe delimited with each nesting within square brackets. For example:  "username|email_address|profile[id|lastname|addresses[id]]|phone_numbers"');
    }

    public static function invalidAllowedOptionsValue()
    {
        return new self('Invalid Allow Options value, must be -1 to unset, 0 for no or 1 for yes. Or you can use boolean values');
    }

    public static function basePathMustBeAString()
    {
        return new self('Base path used is invalid. Must be a string');
    }

    public static function basePathNotRegistered()
    {
        return new self('The requested base path has not been registered');
    }

    public static function alreadyHandleDefinedForRoute(Mapping\RouteMetaData $route)
    {
        return new self('There is a handle already defined for the route ' . $route->getName() . ' on class ' . $route->getClassMetaData()->getClassName());
    }

    public static function handleAnnotationDoesntMatchRouteName($name)
    {
        return new self('The configured handle "' . $name . '" doesn\'t match any route of that name. Ensure @Drest\Handle(for="my_route") matches @Drest\Route(name="my_route")');
    }

    public static function routeRequiresHandle($name)
    {
        return new self('Route requires a handle. Ensure a @Drest\Handle(for="' . $name . '") function is set. These are required for all push type routes (POST/PUT/PATCH)');
    }

    public static function handleForCannotBeEmpty()
    {
        return new self('The @Drest\Handle configuration MUST contain a valid / matching "for" value');
    }

    public static function invalidNamedRouteSyntax()
    {
        return new self('Invalid named route syntax. Must use a formatted string of: {EntityClassName}::{RouteName}. Eg "Entities\\User::get_users"');
    }

    public static function unableToFindRouteByName($routeName, $className)
    {
        return new self('Unable to find the named route "' . $routeName . '" on class ' . $className);
    }

    public static function resourceCanOnlyHaveOneRouteSetAsOrigin()
    {
        return new self('A resource can only have one route set as "origin"');
    }

    public static function unableToHandleACollectionPush()
    {
        return new self('Requests to push data (PUT/POST/PATCH) can only be used on individual elements. Data collections cannot be pushed');
    }

    /**
     * @deprecated
     */
    public static function unknownContentType($contentType)
    {
        return new self('Invalid content type value "' . $contentType . '". Must be one of element or collection. eg. content="element"');
    }


    // Service Exceptions
    public static function actionClassNotAnInstanceOfActionAbstract($class)
    {
        return new self('Action class  "' . $class . '" is not an instance of Drest\Service\Action\ActionAbstract.');
    }

    public static function unknownActionClass($class)
    {
        return new self('Unknown action class "' . $class . '"');
    }

    public static function noMatchedRouteSet()
    {
        return new self('No matched route has been set on this service class. The content type is needed for a default service method call');
    }

    public static function dataWrapNameMustBeAString()
    {
        return new self('Data wrap name must be a string value. Eg array(\'user\' => array(...))');
    }
}



