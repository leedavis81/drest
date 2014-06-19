<?php
namespace Drest\Route;

class NoMatchException extends \Exception
{
    public static function noMatchedRoutes()
    {
        return new self('There are no routes configured to match this request path');
    }
}
