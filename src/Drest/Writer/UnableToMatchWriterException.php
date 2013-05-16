<?php
namespace Drest\Writer;

use Exception;

/**
 * Base exception class for all Drest exceptions.
 *
 * @author Lee
 */
class UnableToMatchWriterException extends Exception
{

	// Set up and configuration
    public static function noMatch()
    {
        return new self('Unable to determine a writer class using both global and service configurations');
    }
}