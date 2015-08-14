<?php

namespace DrestTests\Mapping;

use Drest\Configuration;
use Drest\Mapping\ClassMetaData;
use Drest\Mapping\MetadataFactory;
use Drest\Mapping\Driver\PhpDriver;
use DrestTests\DrestTestCase;

class PhpDriverTest extends \PHPUnit_Framework_TestCase
{

    // test register function with null config file
    public function testRegisterWithNoConfig() {
        $this->setExpectedException('RuntimeException');

        $configuration = new Configuration();

        PhpDriver::register($configuration);
        PhpDriver::create();
    }

    // test register function with a non-existing directory for the configuration file
    public function testRegisterDirNotExists() {
        $this->setExpectedException('RuntimeException');

        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', '/BadConfigFiles');

        PhpDriver::register($configuration);
        PhpDriver::create();     
    }

    // ensuring that the configuration file exists
    public function testRegisterFileNotExists() {
        $this->setExpectedException('RuntimeException');

        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', __DIR__);
        $configuration->setAttribute('configFileName', 'doesnotexist.php');

        PhpDriver::register($configuration);
        PhpDriver::create();
    }

    // testing if configuration file used  is invalid
    public function testRegisterFileInvalid() {
        $this->setExpectedException('RuntimeException');
$file_contents = <<<HEREDOC
<?php
\$resources = null;
return \$resources;
HEREDOC;
        
        $tmp = $this->createCustomTmpFile($file_contents);
       
        $this->registerTmpFile($tmp);
        
        PhpDriver::create();
    }

    // testing if configuration file being used is valid
    public function testRegisterFileValid() {
        $tmp = $this->createGoodTmpFile();
        
        $this->registerTmpFile($tmp);

        $instance = PhpDriver::create([__DIR__ . '/../Entities/NoAnnotation']);

        $classes = $instance->getAllClassNames();

        $this->assertSame($classes, ['Entities\NoAnnotation\User']);
    }

    // test loading metadata from an empty class (resources['\Entities\NoAnnotation\User'] is empty)
    public function testUnableToLoadMetaDataFromClass()
    {
        $this->setExpectedException('RuntimeException');
$file_contents = <<<HEREDOC
<?php
\$resources = [];
\$resources['Entities\NoAnnotation\User'] = [];
return \$resources;
HEREDOC;
        $tmp = $this->createCustomTmpFile('Entities\NoAnnotation\User');

        $this->registerTmpFile($tmp);

        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                [__DIR__ . '/../Entities/NoAnnotation']
            )
        );

        $className = 'Entities\NoAnnotation\User';
        $metadataFactory->getMetadataForClass($className);
        
    }

    public function testDuplicatedRouteName()
    {
        $this->setExpectedException('Drest\DrestException');
$file_contents = <<<HEREDOC
<?php
\$resources = [];
\$resources['Entities\NoAnnotation\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['GET'], 'action' => 'Action\Custom'],
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['GET'], 'action' => 'Action\Custom'],
    ]
];
return \$resources;
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);

        $this->registerTmpFile($tmp);

        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                [__DIR__ . '/../Entities/NoAnnotation']
            )
        );

        $className = 'Entities\NoAnnotation\User';
        $metadataFactory->getMetadataForClass($className);
        
    }

    public function testInvalidVerbUsed()
    {
        $this->setExpectedException('Drest\DrestException');
$file_contents = <<<HEREDOC
<?php
\$resources = [];
\$resources['Entities\NoAnnotation\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['DANCE'], 'action' => 'Action\Custom'],
    ]
];
return \$resources;
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);

        $this->registerTmpFile($tmp);

        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                [__DIR__ . '/../Entities/NoAnnotation']
            )
        );

        $className = 'Entities\NoAnnotation\User';
        $metadataFactory->getMetadataForClass($className);
    }

    public function testEmptyRouteName()
    {
        $this->setExpectedException('Drest\DrestException');
$file_contents = <<<HEREDOC
<?php
\$resources = [];
\$resources['Entities\NoAnnotation\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => '', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['DANCE'], 'action' => 'Action\Custom'],
    ]
];
return \$resources;
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);

        $this->registerTmpFile($tmp);

        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                [__DIR__ . '/../Entities/NoAnnotation']
            )
        );

        $className = 'Entities\NoAnnotation\User';
        $metadataFactory->getMetadataForClass($className);
    }

    public function testNoRepresentationIsAllowed()
    {
$file_contents = <<<HEREDOC
<?php
\$resources = [];
\$resources['Entities\NoAnnotation\User'] = [
    'representations' => [],
    'routes' => [
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['GET'], 'action' => 'Action\Custom'],
        ['name' => 'post_user', 'routePattern' => '/user', 'verbs' => ['POST'], 'expose' => ['username', 'email_address', 'profile' => ['firstname', 'lastname'], 'phone_numbers' => ['number']], 'handle_call' => 'populatePost']
    ]
];
return \$resources;
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);

        $this->registerTmpFile($tmp);

        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                [__DIR__ . '/../Entities/NoAnnotation']
            )
        );

        $className = 'Entities\NoAnnotation\User';
        $cmd = $metadataFactory->getMetadataForClass($className);

        $this->assertEmpty($cmd->getRepresentations());
    }

    public function createGoodTmpFile() {
$file_contents = <<<HEREDOC
<?php
\$resources = [];
\$resources['Entities\NoAnnotation\User'] = [
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
        file_put_contents($tmp, $file_contents);
        return $tmp;
    }

    public function createCustomTmpFile($file_contents) {
        $tmp = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tmp, $file_contents);
        return $tmp;
    }

    /**
     * Registers a custom temporary file
     * @param temporary file $tmp
     */
    public function registerTmpFile($tmp) {
        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', sys_get_temp_dir());
        $configuration->setAttribute('configFileName', basename($tmp));
        PhpDriver::register($configuration);
    }

}