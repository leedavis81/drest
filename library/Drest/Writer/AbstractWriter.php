<?php
namespace Drest\Writer;

use Drest\Mapping\RouteMetaData;

abstract class AbstractWriter implements InterfaceWriter
{
    /**
     * Route metadata for the matched route
     * @var Drest\Mapping\RouteMetaData $routeMetaData
     */
    protected $routeMetaData;

    /**
     * construct an instance of a writer by passing in the matched route metadata
     * @param Drest\Mapping\RouteMetaData $routeMetaData
     */
    public function __construct(RouteMetaData $routeMetaData)
    {
        $this->routeMetaData = $routeMetaData;
    }
}