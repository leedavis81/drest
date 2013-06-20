<?php
namespace Drest\Route;

class MultipleRoutesException extends \Exception
{
    public static function multipleRoutesFound($routes)
    {
        $helperText = '';
        foreach ($routes as $route) {
            /* @var \Drest\Mapping\RouteMetaData $route */
            $helperText .= '"' . $route->getName() . '"';
        }
        return new self('Multiple routes have matched to this request. See route definitions for: ' . $helperText);
    }
}
