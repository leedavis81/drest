<?php
namespace DrestTests;


use Doctrine\Common\Cache\ArrayCache;
use Drest\Configuration;
use Drest\DrestException;

class ConfigurationTest extends DrestTestCase
{

    public function testSettingDebugMode()
    {
        $config = new Configuration();

        $config->setDebugMode(true);
        $this->assertTrue($config->inDebugMode());
        $config->setDebugMode(false);
        $this->assertFalse($config->inDebugMode());
        $config->setDebugMode('string');
        $this->assertTrue($config->inDebugMode());
    }

    public function testSetMetaDataCacheImpl()
    {
        $config = new Configuration();

        $config->setMetadataCacheImpl(new ArrayCache());
        $this->assertInstanceOf('Doctrine\Common\Cache\ArrayCache', $config->getMetadataCacheImpl());
    }

    public function testSetDetectContentOptions()
    {
        $config = new Configuration();

        $config->setDetectContentOptions(array(
            Configuration::DETECT_CONTENT_HEADER => 'james'
        ));
        $this->assertCount(1, $config->getDetectContentOptions());

        $config->setDetectContentOptions(array(
            Configuration::DETECT_CONTENT_HEADER => 'jill'
        ));
        $this->assertCount(1, $config->getDetectContentOptions());

        $config->setDetectContentOptions(array(
        ));
        $this->assertCount(0, $config->getDetectContentOptions());
    }

    /**
     * @expectedException \Drest\DrestException
     */
    public function testSetInvalidDetectContentOptions()
    {
        $config = new Configuration();

        $config->setDetectContentOptions(array(
            'Invalid' => 'james'
        ));
    }

    public function testSet415NoMediaMatch()
    {
        $config = new Configuration();

        $config->set415ForNoMediaMatch(true);
        $this->assertTrue($config->get415ForNoMediaMatchSetting());
        $config->set415ForNoMediaMatch(false);
        $this->assertFalse($config->get415ForNoMediaMatchSetting());
        $config->set415ForNoMediaMatch('string');
        $this->assertTrue($config->get415ForNoMediaMatchSetting());
    }

    public function testExposeRequestOptions()
    {
        $config = new Configuration();

        $options = array(
            Configuration::EXPOSE_REQUEST_HEADER => 'expose'
        );
        $config->setExposeRequestOptions($options);

        $this->assertEquals($options, $config->getExposeRequestOptions());
    }

    /**
     * @expectedException \Drest\DrestException
     */
    public function testInvalidExposeRequestOptions()
    {
        $config = new Configuration();

        $config->setExposeRequestOptions(array(
            'Invalid' => 'expose'
        ));
    }

}
