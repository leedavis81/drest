<?php

namespace DrestTests\Mapping;

use Drest\Mapping\Driver\JsonDriver;
use DrestTests\DrestTestCase;

class JsonDriverTest extends DrestTestCase {

    // test register function with null config file
    public function testEmptyConstruct()
    {
        JsonDriver::create();
    }

    /**
     * test register function with a non-existing directory for the configuration file
     * @expectedException \Drest\Mapping\Driver\DriverException
     */
    public function testRegisterDirNotExists()
    {
        $driver = JsonDriver::create(['/BadConfigFiles']);
        $driver->getAllClassNames();
    }

    /**
     * ensuring that the configuration file exists
     * @expectedException \Drest\Mapping\Driver\DriverException
     */
    public function testRegisterFileNotExists()
    {

        $driver = JsonDriver::create([__DIR__ . '/doesnotexist.php']);
        $driver->getAllClassNames();
    }

    /**
     * testing if configuration file used  is invalid
     * @expectedException \Drest\Mapping\Driver\DriverException
     */
    public function testRegisterFileInvalid()
    {
        $file_contents = <<<HEREDOC
{
    "resources": [
        {
            "entity": "Entities\\User"
        }
    ]
}
HEREDOC;

        $path = $this->createCustomTmpFile($file_contents);
        $driver = JsonDriver::create([$path]);
        $driver->getAllClassNames();
    }

    // testing if configuration file being used is valid
    public function testRegisterFileValid()
    {
        $tmp = $this->createGoodTmpFile('json');
        $instance = JsonDriver::create([sys_get_temp_dir() . '/' . basename($tmp)]);
        $classes = $instance->getAllClassNames();
        $this->assertSame($classes, ['DrestTests\Entities\NoAnnotation\User']);
    }

    public function createGoodTmpFile()
    {
        $json = [
            'resources' => [
                '0' => [
                    'entity' => 'DrestTests\Entities\NoAnnotation\User',
                ]
            ]
        ];
        $file_contents = json_encode($json);
        $tmp = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tmp . '.json', $file_contents);
        return $tmp . '.json';
    }

    public function createCustomTmpFile($file_contents)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tmp . '.json', $file_contents);
        return $tmp . '.json';
    }
}