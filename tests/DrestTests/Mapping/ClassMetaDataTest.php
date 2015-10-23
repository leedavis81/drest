<?php
namespace DrestTests\Mapping;

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

    public function testGetDefaultOriginRoute()
    {
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\AnnotationDriver::create(
                array(__DIR__ . '/../Entities/Typical')
            )
        );
        $className = 'DrestTests\\Entities\\Typical\\User';
        $cmd = $metadataFactory->getMetadataForClass($className);

        $this->assertEquals('get_user', $cmd->getOriginRoute($this->_getTestEntityManager())->getName());
    }

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
