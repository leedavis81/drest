<?php

namespace DrestTests\Mapping;

use Drest\Configuration;
use Drest\Mapping\Driver\YamlDriver;

class YamlDriverTest extends \PHPUnit_Framework_TestCase {

    // test register function with null config file
    public function testRegisterWithNoConfig() {
        $this->setExpectedException('RuntimeException');

        $configuration = new Configuration();
        $configuration->setAttribute('configFileName', 'doesnotexist.yaml');

        YamlDriver::register($configuration);
        YamlDriver::create();
    }

    // test register function with a non-existing directory for the configuration file
    public function testRegisterDirNotExists() {
        $this->setExpectedException('RuntimeException');

        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', '/BadConfigFiles');
        $configuration->setAttribute('configFileName', 'doesnotexist.yaml');

        YamlDriver::register($configuration);
        YamlDriver::create();     
    }

    // ensuring that the configuration file exists
    public function testRegisterFileNotExists() {
        $this->setExpectedException('RuntimeException');

        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', __DIR__);
        $configuration->setAttribute('configFileName', 'doesnotexist.yaml');

        YamlDriver::register($configuration);
        YamlDriver::create();
    }

     // testing if configuration file used  is invalid
    public function testRegisterFileInvalid() {
        $this->setExpectedException('RuntimeException');
        $file_contents = '';
        
        $tmp = $this->createCustomTmpFile($file_contents);
       
        $this->registerTmpFile($tmp);
        
        YamlDriver::create();
    }

    // testing if configuration file being used is valid
    public function testRegisterFileValid() {
        $tmp = $this->createGoodTmpFile();
        
        $this->registerTmpFile($tmp);

        $instance = YamlDriver::create([__DIR__ . '/../Entities/NoAnnotation']);

        $classes = $instance->getAllClassNames();

        $this->assertSame($classes, ['Entities\NoAnnotation\User']);
    }

    public function createGoodTmpFile() {
$file_contents = <<<HEREDOC
--- resources
Entities\NoAnnotation\User:
    representations: 
        - Json
    routes:
        - name: get_user
          routePattern: /user/:id
          routeConditions:
            id: \d+
          verbs:
            - GET
          action: Action\Custom
HEREDOC;
        $tmp = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tmp . '.yaml', $file_contents);
        return $tmp . '.yaml';
    }

    public function createCustomTmpFile($file_contents) {
        $tmp = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tmp . '.yaml', $file_contents);
        return $tmp . '.yaml';
    }

    /**
     * Registers a custom temporary file
     * @param temporary file $tmp
     */
    public function registerTmpFile($tmp) {
        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', sys_get_temp_dir());
        $configuration->setAttribute('configFileName', basename($tmp));
        YamlDriver::register($configuration);
    }
}