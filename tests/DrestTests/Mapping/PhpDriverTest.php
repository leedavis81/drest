<?php

namespace DrestTests\Mapping;

use Drest\Mapping\MetadataFactory;
use Drest\Mapping\Driver\PhpDriver;

class PhpDriverTest extends \PHPUnit_Framework_TestCase
{

    // test register function with null config file
    public function testEmptyConstruct() {

        PhpDriver::create();
    }

    /**
     * test register function with a non-existing directory for the configuration file
     * @expectedException \Drest\Mapping\Driver\DriverException
     */
    public function testRegisterDirNotExists() {

        $driver = PhpDriver::create(['/BadConfigFiles']);
        $driver->getAllClassNames();
    }

    /**
     * ensuring that the configuration file exists
     * @expectedException \Drest\Mapping\Driver\DriverException
     */
    public function testRegisterFileNotExists() {

        $driver = PhpDriver::create([__DIR__ . '/doesnotexist.php']);
        $driver->getAllClassNames();
    }

    /**
     * testing if configuration file used  is invalid
     * @expectedException \Drest\Mapping\Driver\DriverException
     */
    public function testRegisterFileInvalid() {
        $file_contents = <<<HEREDOC
<?php
\$resources = null;
return \$resources;
HEREDOC;
        
        $path = $this->createCustomTmpFile($file_contents);
        $driver = PhpDriver::create([$path]);
        $driver->getAllClassNames();
    }

    // testing if configuration file being used is valid
    public function testRegisterFileValid() {
        $tmp = $this->createGoodTmpFile();

        $instance = PhpDriver::create([$tmp]);

        $classes = $instance->getAllClassNames();

        $this->assertSame($classes, ['DrestTests\Entities\NoAnnotation\User']);
    }

    /**
     * test loading metadata from an empty class (resources['\DrestTests\Entities\NoAnnotation\User'] is empty)
     * @expectedException \Drest\DrestException
     */
    public function testUnableToLoadMetaDataFromClass()
    {
$file_contents = <<<HEREDOC
<?php
\$resources = [];
return \$resources;
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);

        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                [$tmp]
            )
        );

        $className = 'DrestTests\Entities\NoAnnotation\User';
        $metadataFactory->getMetadataForClass($className);
    }

    public function testDuplicatedRouteName()
    {
        $this->setExpectedException('Drest\DrestException');
$file_contents = <<<HEREDOC
<?php
\$resources = [];
\$resources['DrestTests\Entities\NoAnnotation\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['GET'], 'action' => 'Action\Custom'],
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['GET'], 'action' => 'Action\Custom'],
    ]
];
return \$resources;
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);

        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                [$tmp]
            )
        );

        $className = 'DrestTests\Entities\NoAnnotation\User';
        $metadataFactory->getMetadataForClass($className);
        
    }

    public function testInvalidVerbUsed()
    {
        $this->setExpectedException('Drest\DrestException');
$file_contents = <<<HEREDOC
<?php
\$resources = [];
\$resources['DrestTests\Entities\NoAnnotation\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['DANCE'], 'action' => 'Action\Custom'],
    ]
];
return \$resources;
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);

        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                [$tmp]
            )
        );

        $className = 'DrestTests\Entities\NoAnnotation\User';
        $metadataFactory->getMetadataForClass($className);
    }

    public function testEmptyRouteName()
    {
        $this->setExpectedException('Drest\DrestException');
$file_contents = <<<HEREDOC
<?php
\$resources = [];
\$resources['DrestTests\Entities\NoAnnotation\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => '', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['DANCE'], 'action' => 'Action\Custom'],
    ]
];
return \$resources;
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);

        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create([$tmp])
        );

        $className = 'DrestTests\Entities\NoAnnotation\User';
        $metadataFactory->getMetadataForClass($className);
    }

    public function testNoRepresentationIsAllowed()
    {
$file_contents = <<<HEREDOC
<?php
\$resources = [];
\$resources['DrestTests\Entities\NoAnnotation\User'] = [
    'representations' => [],
    'routes' => [
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['GET'], 'action' => 'Action\Custom'],
        ['name' => 'post_user', 'routePattern' => '/user', 'verbs' => ['POST'], 'expose' => ['username', 'email_address', 'profile' => ['firstname', 'lastname'], 'phone_numbers' => ['number']], 'handle_call' => 'populatePost']
    ]
];
return \$resources;
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);

        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create([$tmp])
        );

        $className = 'DrestTests\Entities\NoAnnotation\User';
        $cmd = $metadataFactory->getMetadataForClass($className);

        $this->assertEmpty($cmd->getRepresentations());
    }

    public function createGoodTmpFile() {
$file_contents = <<<HEREDOC
<?php
\$resources = [];
\$resources['DrestTests\Entities\NoAnnotation\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['GET'], 'action' => 'Action\Custom', 'origin' => 'get_user'],
        ['name' => 'get_user_profile', 'routePattern' => '/user/:id/profile', 'verbs' => ['GET'], 'expose' => ['profile']],
        ['name' => 'get_user_numbers', 'routePattern' => '/user/:id/numbers', 'verbs' => ['GET'], 'expose' => ['phone_numbers']],
        ['name' => 'post_user', 'routePattern' => '/user', 'verbs' => ['POST'], 'expose' => ['username', 'email_address', 'profile' => ['firstname', 'lastname'], 'phone_numbers' => ['number']], 'handle_call' => 'populatePost'],
        ['name' => 'get_users', 'routePattern' => '/users', 'verbs' => ['GET'], 'collection' => 'true', 'expose' => ['username', 'email_address', 'profile', 'phone_numbers']],
        ['name' => 'update_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['PUT', 'PATCH'], 'expose' => ['email_address', 'profile' => ['firstname', 'lastname']], 'handle_call' => 'patchUser'],
        ['name' => 'delete_user', 'routePattern' => '/user/:id', 'verbs' => ['DELETE']],
        ['name' => 'delete_users', 'routePattern' => '/users', 'collection' => 'true', 'verbs' => ['DELETE']]
    ]
];
return \$resources;
HEREDOC;
        $tmp = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tmp . '.php', $file_contents);
        include_once $tmp . '.php';
        return $tmp . '.php';
    }

    public function createCustomTmpFile($file_contents) {
        $tmp = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tmp . '.php', $file_contents);
        include_once $tmp . '.php';
        return $tmp . '.php';
    }
}