<?php

namespace Drest\Mapping\Driver;

use Drest\DrestException;


abstract class AbstractDriver implements DriverInterface {

    /**
     * The paths to look for mapping files - immutable as classNames as cached, must be passed on construct.
     * @var array
     */
    protected $paths;

    /**
     * Extensions of the files to read
     * @var array $paths
     */
    protected $extensions = [];

    /**
     * Load metadata for the given class name
     * @param string $className
     * @return \Drest\Mapping\ClassMetadata
     */
    abstract public function loadMetadataForClass($className);


    abstract protected function isDrestResource($className);


    public function __construct($paths = []) {
        $this->paths = (array) $paths;

        $this->addExtension('php');
    }

    /**
     * Get all the metadata class names known to this driver.
     * @throws DrestException
     * @return array          $classes
     */
    public function getAllClassNames()
    {
        if (empty($this->classNames)) {
            if (empty($this->paths)) {
                throw DrestException::pathToConfigFilesRequired();
            }
            $classes = [];
            $included = [];
            foreach ($this->paths as $path) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($iterator as $file) {
                    /* @var \SplFileInfo $file */
                    if (!in_array($file->getExtension(), $this->extensions)) {
                        continue;
                    }

                    $path = $file->getRealPath();
                    if (!empty($path)) {
                        require_once $path;
                    }

                    // Register the files we've included here
                    $included[] = $path;
                }
            }

            foreach (get_declared_classes() as $className) {
                $reflClass = new \ReflectionClass($className);
                $sourceFile = $reflClass->getFileName();
                if (in_array($sourceFile, $included) && $this->isDrestResource($className)) {
                    $classes[] = $className;
                }
            }

            $this->classNames = $classes;
        }

        return $this->classNames;
    }

    /**
     * Get paths to annotation classes
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Add an extension to look for classes
     * @param string $extension - can be a string or an array of extensions
     */
    public function addExtension($extension)
    {
        $extension = (array) $extension;
        foreach ($extension as $ext) {
            if (!in_array($ext, $this->extensions)) {
                $this->extensions[] = strtolower(preg_replace("/[^a-zA-Z0-9.\s]/", "", $ext));
            }
        }
    }

    /**
     * Remove all registered extensions, if an extension name is passed, only remove that entry
     * @param string $extension
     */
    public function removeExtensions($extension = null)
    {
        if (is_null($extension)) {
            $this->extensions = [];
        } else {
            $offset = array_search($extension, $this->extensions);
            if ($offset !== false) {
                unset($this->extensions[$offset]);
            }
        }
    }

}