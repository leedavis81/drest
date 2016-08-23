<?php
namespace DrestTests\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Drest\Mapping\ClassMetaData;
use Drest\Mapping\MetadataFactory;
use DrestCommon\Representation\Json;
use DrestTests\DrestTestCase;

class AnnotationDriverTest extends DrestTestCase
{
    
    /**
     * @expectedException \Drest\DrestException
     */
    public function testMetadataResourceRequiresAtLeastOneServiceDefinition()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/NoServiceDefinition')
            )
        );

        $className = 'DrestTests\\Entities\\NoServiceDefinition\\NoServiceDefinition';
        $metadataFactory->getMetadataForClass($className);
    }


    /**
     * @expectedException \Drest\DrestException
     */
    public function testUnableToLoadMetaDataFromClass()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/MissingMetaData')
            )
        );

        $className = 'DrestTests\\Entities\\MissingMetaData\\MissingMetaData';
        $metadataFactory->getMetadataForClass($className);
    }


    /**
     * @expectedException \Drest\DrestException
     */
    public function testDuplicatedRouteName()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/DuplicatedRouteName')
            )
        );

        $className = 'DrestTests\\Entities\\DuplicatedRouteName\\DuplicatedRouteName';
        $metadataFactory->getMetadataForClass($className);
    }

    /**
     * @expectedException \Drest\DrestException
     */
    public function testInvalidVerbUsed()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/InvalidVerbUsed')
            )
        );

        $className = 'DrestTests\\Entities\\InvalidVerbUsed\\InvalidVerbUsed';
        $metadataFactory->getMetadataForClass($className);
    }

    /**
     * @expectedException \Drest\DrestException
     */
    public function testEmptyRouteName()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/EmptyRouteName')
            )
        );

        $className = 'DrestTests\\Entities\\EmptyRouteName\\EmptyRouteName';
        $metadataFactory->getMetadataForClass($className);
    }

    /**
     * @expectedException \Drest\DrestException
     */
    public function testHandleAlreadyDefined()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/HandleAlreadyDefined')
            )
        );

        $className = 'DrestTests\\Entities\\HandleAlreadyDefined\\HandleAlreadyDefined';
        $metadataFactory->getMetadataForClass($className);
    }


    public function testGetHandleUsed()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/GetHandleUsed')
            )
        );

        $className = 'DrestTests\\Entities\\GetHandleUsed\\GetHandleUsed';
        $metaData = $metadataFactory->getMetadataForClass($className);
        $routeMetaData = $metaData->getRouteMetaData('get_user');
        $handle = $routeMetaData->getHandleCall();
        $result = $className::$handle(['a', 'b', 'c']);
        $this->assertSame([1, 2, 3], $result);
    }

    /**
     * @expectedException \Drest\DrestException
     */
    public function testHandleDoesntMatchRouteName()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/HandleDoesntMatchRouteName')
            )
        );

        $className = 'DrestTests\\Entities\\HandleDoesntMatchRouteName\\HandleDoesntMatchRouteName';
        $metadataFactory->getMetadataForClass($className);
    }

    public function testNoRepresentationIsAllowed()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/Typical')
            )
        );
        $className = 'DrestTests\\Entities\\Typical\\NoRepresentation';
        $cmd = $metadataFactory->getMetadataForClass($className);

        $this->assertEmpty($cmd->getRepresentations());
    }

    public function testAnnotatedOriginRoute()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/Typical')
            )
        );
        $className = 'DrestTests\\Entities\\Typical\\Profile';
        $cmd = $metadataFactory->getMetadataForClass($className);

        $this->assertEquals('get_profiles', $cmd->getOriginRoute($this->_getTestEntityManager())->getName());
    }

    public function testNoAnnotatedOriginRoute()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/Typical')
            )
        );
        $className = 'DrestTests\\Entities\\Typical\\Address';
        $cmd = $metadataFactory->getMetadataForClass($className);

        $this->assertNull($cmd->getOriginRoute($this->_getTestEntityManager()));
    }

    /**
     * @expectedException \Drest\DrestException
     */
    public function testMissingPathToConfigFiles()
    {
        $driver = \Drest\Mapping\Driver\AnnotationDriver::create();
        $driver->getAllClassNames();
    }

    public function testRemovingExtension()
    {
        $annotationDriver = \Drest\Mapping\Driver\AnnotationDriver::create(
            array(__DIR__ . '/../Entities/Typical')
        );

        $annotationDriver->removeExtensions();
        $metadataFactory = new MetadataFactory($annotationDriver);

        $this->assertCount(0, $metadataFactory->getAllClassNames());
        $annotationDriver->addExtension('php');
        $this->assertGreaterThan(0, $metadataFactory->getAllClassNames());
    }
}
