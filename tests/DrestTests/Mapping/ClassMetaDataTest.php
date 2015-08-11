<?php
namespace DrestTests\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Drest\Mapping\ClassMetaData;
use Drest\Mapping\MetadataFactory;
use DrestCommon\Representation\Json;
use DrestTests\DrestTestCase;

class ClassMetaDataTest extends DrestTestCase
{

    public function testClassMetaDataIsSerializable()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
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
                array(__DIR__ . '/../Entities/Typical')
            )
        );

        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = $metadataFactory->getMetadataForClass($className);

        $this->assertEquals('user', $cmd->getElementName());
    }

}
