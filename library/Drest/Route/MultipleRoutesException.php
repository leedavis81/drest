<?php
namespace Drest\Route;

class MultipleRoutesException extends \Exception
{
    public static function multipleRoutesFound($services)
    {
        $helperText = '';
        foreach ($services as $service)
        {
            $helperText .= '"' . $service->getName() . '"';
        }
        return new self('Multiple routes have matched to this request. See service definitions for: ' . $helperText);
    }
}
