<?php
namespace Drest\Representation;

use Exception;

/**
 * Base exception class for all Drest exceptions.
 *
 * @author Lee
 */
class UnableToMatchRepresentationException extends Exception
{
    // Set up and configuration
    public static function noMatch()
    {
        return new self('Unable to determine a representation class using both global and service configurations');
    }
}