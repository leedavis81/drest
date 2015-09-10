<?php

namespace Drest\Mapping\Driver;

use Doctrine\Common\Annotations;
use Drest\Configuration;
use Drest\DrestException;
use Drest\Mapping\Annotation;
use Drest\Mapping;
use Drest\Mapping\RouteMetaData;

/**
 * The AnnotationDriver reads the mapping metadata from doc block annotations.
 * Doesn't require paths / file extensions as entities are pull from the doctrine entity manager
 */
class AnnotationDriver extends AbstractDriver
{

    /**
     * Annotations reader
     * @var \Doctrine\Common\Annotations\AnnotationReader $reader
     */
    private $reader;

    /**
     * Loaded class names
     * @var array
     */
    protected $classNames = [];

    public function __construct(Annotations\AnnotationReader $reader, $paths = [])
    {
        parent::__construct($paths);
        $this->reader = $reader;
    }

    /**
     * Does the class contain a drest resource object
     * @param  string $className
     * @return bool
     */
    public function isDrestResource($className)
    {
        $classAnnotations = $this->reader->getClassAnnotations(new \ReflectionClass($className));

        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation instanceof Annotation\Resource) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load metadata for a class name
     * @param  object|string         $class - Pass in either the class name, or an instance of that class
     * @return Mapping\ClassMetaData $metaData - return null if metadata couldn't be populated from annotations
     * @throws DrestException
     */
    public function loadMetadataForClass($class)
    {
        $resourceFound = false;

        if (is_string($class)) {
            $class = new \ReflectionClass($class);
        }

        $metadata = new Mapping\ClassMetaData($class);
        foreach ($this->reader->getClassAnnotations($class) as $annotatedObject) {
            if ($annotatedObject instanceof Annotation\Resource) {
                $resourceFound = true;

                if ($annotatedObject->routes === null) {
                    throw DrestException::annotatedResourceRequiresAtLeastOneServiceDefinition($class->name);
                }

                if (is_array($annotatedObject->representations))
                {
                    $metadata->addRepresentations($annotatedObject->representations);
                }

                $this->processRoutes($annotatedObject->routes, $metadata);

                $this->processMethods($class->getMethods(), $metadata);

                $this->checkHandleCalls($metadata->getRoutesMetaData());

            }
        }

        return ($resourceFound) ? $metadata : null;
    }


    /**
     * Process the method
     * @param $methods
     * @param Mapping\ClassMetaData $metadata
     * @throws DrestException
     */
    protected function processMethods($methods, Mapping\ClassMetaData $metadata)
    {
        // Set the handle calls
        foreach ($methods as $method) {
            /* @var \ReflectionMethod $method */
            if ($method->isPublic()) {
                foreach ($this->reader->getMethodAnnotations($method) as $methodAnnotation) {
                    if ($methodAnnotation instanceof Annotation\Handle) {
                        // Make sure the for is not empty
                        if (empty($methodAnnotation->for) || !is_string($methodAnnotation->for)) {
                            throw DrestException::handleForCannotBeEmpty();
                        }
                        if (($routeMetaData = $metadata->getRouteMetaData($methodAnnotation->for)) === false) {
                            throw DrestException::handleAnnotationDoesntMatchRouteName($methodAnnotation->for);
                        }
                        if ($routeMetaData->hasHandleCall()) {
                            // There is already a handle set for this route
                            throw DrestException::handleAlreadyDefinedForRoute($routeMetaData);
                        }
                        $routeMetaData->setHandleCall($method->getName());
                    }
                }
            }
        }
    }

    /**
     * Factory method for the Annotation Driver
     *
     * @param  array|string                 $paths
     * @return AnnotationDriver
     */
    public static function create($paths = [])
    {
        $reader = new Annotations\AnnotationReader();

        return new self($reader, (array) $paths);
    }

    /**
     * Driver registration template method.
     */
    public static function register() {
        self::registerAnnotations();
    }

    /**
     * Register out annotation classes with the annotation registry.
     */
    public static function registerAnnotations()
    {
        Annotations\AnnotationRegistry::registerFile(__DIR__ . '/DrestAnnotations.php');
    }
}
