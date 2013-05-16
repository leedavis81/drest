<?php
namespace Drest\Query;

class InvalidExposeFieldsException extends \Exception
{
    public static function invalidExposeFieldsString()
    {
        return new self('Characters used in the expose fields string are invalid. Only character allowed are [a-z], [A-Z], [0-9], "[", "]", "|" and "_"');
    }

    public static function unableToParseExposeString($string)
    {
        return new self('Unable to parse the given expose string. Ensure syntax is correct, and square brackets are correctly opened / closed. ' . $string);
    }
}
