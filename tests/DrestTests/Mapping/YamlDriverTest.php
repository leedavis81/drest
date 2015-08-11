<?php

namespace DrestTests\Mapping;

use Drest\Configuration;
use Drest\Mapping\ClassMetaData;
use Drest\Mapping\MetadataFactory;
use Drest\Mapping\Driver\YamlDriver;

class YamlDriverTest extends \PHPUnit_Framework_TestCase
{

    // test register function with null config file
    public function testRegisterWithNoConfig() {
        $this->setExpectedException('RuntimeException');
        $configuration = new Configuration();
        YamlDriver::register($configuration);
    }

    // test register function with a non-existing directory for the configuration file
    public function testRegisterDirNotExists() {
        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', '/BadConfigFiles');
        YamlDriver::register($configuration);     
    }

    // ensuring that the configuration file exists
    public function testRegisterFileNotExists() {
        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', __DIR__);
        $configuration->setAttribute('configFileName', 'doesnotexist.yaml');
        YamlDriver::register($configuration);
    }

    public function testRegisterFileInvalid() {
        $this->setExpectedException('RuntimeException');
        $configuration = new Configuration();
        YamlDriver::register($configuration);
        $configuration->setAttribute('configFilePath', '$');
    }

    public function testRegisterFileValid() {
        $configFilePath = '../ConfigFiles/';
        $configFileName = '../ConfigFiles/config.yaml';
        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', $configFilePath);
        $configuration->setAttribute('configFileName', $configFileName);
        YamlDriver::register($configuration);
    }
}