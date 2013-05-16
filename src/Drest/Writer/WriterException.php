<?php
namespace Drest\Writer;

use Exception;

/**
 * Base exception class for all Drest exceptions.
 *
 * @author Lee
 */
class WriterException extends Exception
{

    public static function unknownWriterClass($class_name)
    {
    	return new self('Unknown writer class "' . $class_name . '". Defined writer classes must be an instance of Drest\\Writer\\Interface');
    }

    public static function writerMustBeObjectOrString()
    {
		return new self('Writer must be an object of Drest\\Writer\\InterfaceWriter or a string representing the class name');
    }

    public static function writerMustBeInstanceOfDrestWriter()
    {
        return new self('Writer must be an instance of Drest\\Writer\\InterfaceWriter, please ensure any custom writers classes implement this');
    }

    public static function unableToDetermineAWriter()
    {
        return new self('Unable to determine a writer class using both global and service configurations');
    }

    public static function unableToMatchAWriter()
    {
        return new self('Unable to match a writer instance using Configuration::DETECT_CONTENT_* methods set');
    }

    public static function noWritersSetForRoute(Mapping\RouteMetaData $route)
    {
        return new self('No writers have been set for the service "' . $route->getName() . '" for the Entity "' . $route->getClassMetaData()->getClassName() . "'");
    }
}