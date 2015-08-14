<?php

namespace DrestTests\Mapping;

use Drest\Configuration;
use Drest\Mapping\Driver\JsonDriver;

class JsonDriverTest extends \PHPUnit_Framework_TestCase {

    // test register function with null config file
    public function testRegisterWithNoConfig() {
        $this->setExpectedException('RuntimeException');

        $configuration = new Configuration();

        JsonDriver::register($configuration);
        JsonDriver::create();
    }

    // test register function with a non-existing directory for the configuration file
    public function testRegisterDirNotExists() {
        $this->setExpectedException('RuntimeException');

        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', '/BadConfigFiles');

        JsonDriver::register($configuration);
        JsonDriver::create();     
    }

    // ensuring that the configuration file exists
    public function testRegisterFileNotExists() {
        $this->setExpectedException('RuntimeException');

        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', __DIR__);
        $configuration->setAttribute('configFileName', 'doesnotexist.json');

        JsonDriver::register($configuration);
        JsonDriver::create();
    }

     // testing if configuration file used  is invalid
    public function testRegisterFileInvalid() {
        $this->setExpectedException('RuntimeException');
$file_contents = <<<HEREDOC
{
    "resources": [
        {
            "entity": "Entities\\User"
        }
    ]
}
HEREDOC;
        
        $tmp = $this->createCustomTmpFile($file_contents);
       
        $this->registerTmpFile($tmp);
        
        JsonDriver::create();
    }

    // testing if configuration file being used is valid
    public function testRegisterFileValid() {
        $tmp = $this->createGoodTmpFile('json');
        
        $this->registerTmpFile($tmp);

        $instance = JsonDriver::create([__DIR__ . '/../Entities/NoAnnotation']);

        $classes = $instance->getAllClassNames();

        $this->assertSame($classes, ['Entities\NoAnnotation\User']);
    }

    public function createGoodTmpFile() {
        $json = [
            'resources' => [
                '0' => [
                    'entity' => 'Entities\NoAnnotation\User',
                ]
            ]
        ];
        $file_contents = json_encode($json);
        $tmp = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tmp . '.json', $file_contents);
        return $tmp . '.json';
    }

    public function createCustomTmpFile($file_contents) {
        $tmp = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tmp . '.json', $file_contents);
        return $tmp . '.json';
    }

    /**
     * Registers a custom temporary file
     * @param temporary file $tmp
     */
    public function registerTmpFile($tmp) {
        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', sys_get_temp_dir());
        $configuration->setAttribute('configFileName', basename($tmp));
        JsonDriver::register($configuration);
    }
}