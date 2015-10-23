<?php

namespace DrestTests\Mapping;

use Drest\Mapping\Driver\YamlDriver;
use DrestTests\DrestTestCase;

class YamlDriverTest extends DrestTestCase {

    // test register function with null config file
    public function testEmptyConstruct()
    {
        YamlDriver::create();
    }

    /**
     * test register function with a non-existing directory for the configuration file
     * @expectedException \Drest\Mapping\Driver\DriverException
     */
    public function testRegisterDirNotExists()
    {
        $driver = YamlDriver::create(['/BadConfigFiles']);
        $driver->getAllClassNames();
    }

    /**
     * ensuring that the configuration file exists
     * @expectedException \Drest\Mapping\Driver\DriverException
     */
    public function testRegisterFileNotExists()
    {

        $driver = YamlDriver::create([__DIR__ . '/doesnotexist.yaml']);
        $driver->getAllClassNames();
    }

    /**
     * testing if configuration file used  is invalid
     * @expectedException \Drest\Mapping\Driver\DriverException
     */
    public function testRegisterFileInvalid() {

        $file_contents = '';

        $path = $this->createCustomTmpFile($file_contents);
        $driver = YamlDriver::create([$path]);
        $driver->getAllClassNames();
    }

    // testing if configuration file being used is valid
    public function testRegisterFileValid()
    {
        $tmp = $this->createGoodTmpFile();
        $instance = YamlDriver::create([sys_get_temp_dir() . '/' . basename($tmp)]);
        $classes = $instance->getAllClassNames();
        $this->assertSame($classes, ['DrestTests\Entities\NoAnnotation\User']);
    }


    public function createGoodTmpFile()
    {
        $file_contents = <<<HEREDOC
--- resources
DrestTests\Entities\NoAnnotation\User:
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

    public function createCustomTmpFile($file_contents)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tmp . '.yaml', $file_contents);
        return $tmp . '.yaml';
    }
}