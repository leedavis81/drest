<?php
namespace Drest\Route;

class MultipleRoutesException extends \Exception
{
    public static function multipleRoutesFound($routes)
    {
        $helperText = '';
        foreach ($routes as $route)
        {
            $helperText .= '"' . $route->getName() . '"';
        }
        return new self('Multiple routes have matched to this request. See service definitions for: ' . $helperText);
    }
}
