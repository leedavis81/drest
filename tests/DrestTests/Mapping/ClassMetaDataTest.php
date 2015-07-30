<?php
namespace DrestTests\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Drest\Mapping\ClassMetaData;
use Drest\Mapping\MetadataFactory;
use DrestCommon\Representation\Json;
use DrestTests\DrestTestCase;

class ClassMetaDataTest extends DrestTestCase
{

    public function testMetadataConstruction()
    {
        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = new ClassMetaData(new \ReflectionClass($className));

        $this->assertEquals($className, $cmd->getClassName());
    }

    public function testMetaDataCanAddRepresentation()
    {
        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = new ClassMetaData(new \ReflectionClass($className));

        $rep = new Json();
        $cmd->addRepresentation($rep);

        $this->assertContains($rep, $cmd->getRepresentations());
    }

    public function testMetadataCanAddStringRepresentation()
    {
        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = new ClassMetaData(new \ReflectionClass($className));

        $rep = 'SomeClass';
        $cmd->addRepresentation($rep);

        $this->assertContains($rep, $cmd->getRepresentations());
    }

    /**
     * @expectedException \DrestCommon\Representation\RepresentationException
     */
    public function testMetadataInvalidRepresentationObject()
    {
        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = new ClassMetaData(new \ReflectionClass($className));

        $rep = new \StdClass();
        $cmd->addRepresentation($rep);
    }

    /**
     * @expectedException \DrestCommon\Representation\RepresentationException
     */
    public function testMetadataRepresentationCanNotBeAnArray()
    {
        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = new ClassMetaData(new \ReflectionClass($className));

        $rep = array('a', 'b');
        $cmd->addRepresentation($rep);
    }

    /**
     * @expectedException \DrestCommon\Representation\RepresentationException
     */
    public function testMetadataRepresentationCanNotBeAnInteger()
    {
        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = new ClassMetaData(new \ReflectionClass($className));

        $rep = 1;
        $cmd->addRepresentation($rep);
    }


    /**
     * @expectedException \Drest\DrestException
     */
    public function testMetadataResourceRequiresAtLeastOneServiceDefinition()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                new AnnotationReader(),
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
                new AnnotationReader(),
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
                new AnnotationReader(),
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
                new AnnotationReader(),
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
                new AnnotationReader(),
                array(__DIR__ . '/../Entities/EmptyRouteName')
            )
        );

        $className = 'DrestTests\\Entities\\EmptyRouteName\\EmptyRouteName';
        $metadataFactory->getMetadataForClass($className);
    }

    /**
     * @expectedException \Drest\DrestException
     */
    public function testMissingPathToConfigFiles()
    {
        $driver = \Drest\Mapping\Driver\AnnotationDriver::create(new AnnotationReader());
        $driver->getAllClassNames();
    }

    /**
     * @expectedException \Drest\DrestException
     */
    public function testHandleAlreadyDefined()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                new AnnotationReader(),
                array(__DIR__ . '/../Entities/HandleAlreadyDefined')
            )
        );

        $className = 'DrestTests\\Entities\\HandleAlreadyDefined\\HandleAlreadyDefined';
        $metadataFactory->getMetadataForClass($className);
    }

    /**
     * @expectedException \Drest\DrestException
     */
    public function testHandleDoesntMatchRouteName()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                new AnnotationReader(),
                array(__DIR__ . '/../Entities/HandleDoesntMatchRouteName')
            )
        );

        $className = 'DrestTests\\Entities\\HandleDoesntMatchRouteName\\HandleDoesntMatchRouteName';
        $metadataFactory->getMetadataForClass($className);
    }



    public function testGetDefaultOriginRoute()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                new AnnotationReader(),
                array(__DIR__ . '/../Entities/Typical')
            )
        );
        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = $metadataFactory->getMetadataForClass($className);

        $this->assertEquals('get_user', $cmd->getOriginRoute($this->_getTestEntityManager())->getName());
    }

    public function testNoRepresentationIsAllowed()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                new AnnotationReader(),
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
                new AnnotationReader(),
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
                new AnnotationReader(),
                array(__DIR__ . '/../Entities/Typical')
            )
        );
        $className = 'DrestTests\\Entities\\Typical\\Address';
        $cmd = $metadataFactory->getMetadataForClass($className);

        $this->assertNull($cmd->getOriginRoute($this->_getTestEntityManager()));
    }

    public function testClassMetaDataIsSerializable()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                new AnnotationReader(),
                array(__DIR__ . '/../Entities/Typical')
            )
        );

        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = $metadataFactory->getMetadataForClass($className);
        $serialized = serialize($cmd);
        $cmd2 = unserialize($serialized);

        $this->assertEquals($cmd->getRoutesMetaData(), $cmd2->getRoutesMetaData());
    }

    public function testClassMetadataNotExpired()
    {
        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = new ClassMetaData(new \ReflectionClass($className));

        $this->assertFalse($cmd->expired());
    }

    public function testClassMetadataElementName()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                new AnnotationReader(),
                array(__DIR__ . '/../Entities/Typical')
            )
        );

        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = $metadataFactory->getMetadataForClass($className);

        $this->assertEquals('user', $cmd->getElementName());
    }


    public function testRemovingExtension()
    {
        $annotationDriver = \Drest\Mapping\Driver\AnnotationDriver::create(
            new AnnotationReader(),
            array(__DIR__ . '/../Entities/Typical')
        );

        $annotationDriver->removeExtensions();
        $metadataFactory = new MetadataFactory($annotationDriver);

        $this->assertCount(0, $metadataFactory->getAllClassNames());
        $annotationDriver->addExtension('php');
        $this->assertGreaterThan(0, $metadataFactory->getAllClassNames());
    }
}
