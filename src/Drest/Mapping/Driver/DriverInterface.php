<?php
namespace Drest\Mapping\Driver;

interface DriverInterface
{
    /**
     * Load metadata for the given class name
     * @param string $className
     * @return \Drest\Mapping\ClassMetadata
     */
    public function loadMetadataForClass($className);

    /**
     * Get every class that has been registered to this driver
     * @return array $classNames
     */
    public function getAllClassNames();
}
