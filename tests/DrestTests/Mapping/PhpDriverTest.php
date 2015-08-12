<?php

namespace DrestTests\Mapping;

use Drest\Configuration;
use Drest\Mapping\ClassMetaData;
use Drest\Mapping\MetadataFactory;
use Drest\Mapping\Driver\PhpDriver;
use DrestCommon\Representation\Json;

class PhpDriverTest extends \PHPUnit_Framework_TestCase
{

    // test register function with null config file
    public function testRegisterWithNoConfig() {
        $this->setExpectedException('RuntimeException');
        $configuration = new Configuration();
        PhpDriver::register($configuration);
    }

    // test register function with a non-existing directory for the configuration file
    public function testRegisterDirNotExists() {
        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', '/BadConfigFiles');
        PhpDriver::register($configuration);     
    }

    // ensuring that the configuration file exists
    public function testRegisterFileNotExists() {
        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', __DIR__);
        $configuration->setAttribute('configFileName', 'doesnotexist.php');
        PhpDriver::register($configuration);
    }

    public function testRegisterFileInvalid() {
        $this->setExpectedException('RuntimeException');
        $configuration = new Configuration();
        PhpDriver::register($configuration);
        $configuration->setAttribute('configFilePath', '$');
    }

    public function testRegisterFileValid() {
        $configFilePath = '../ConfigFiles/';
        $configFileName = '../ConfigFiles/config.php';
        $configuration = new Configuration();
        $configuration->setAttribute('configFilePath', $configFilePath);
        $configuration->setAttribute('configFileName', $configFileName);
        PhpDriver::register($configuration);
    }

    /* Below are the tests ported over from AnnotationDriver. */

    // Adding a representation to the config
    public function testMetaDataCanAddRepresentation()
    {
        include($this->createGoodTmpFile());
        if(in_array('representations', $resources['\Entities\User']) && !in_array('Json', $resources['\Entities\User']['representations'])) {
            array_push($resources['\Entities\User']['representations'], 'Json');
        }
        $this->assertContains('Json', $resources['\Entities\User']['representations']);
    }

    /**
     * @expectedException \DrestCommon\Representation\RepresentationException
     */
    public function testMetadataInvalidRepresentationObject()
    {
        $this->setExpectedException('\DrestCommon\Representation\RepresentationException');
        $className = 'DrestTests\\Entities\\NoAnnotation\\User';
        $cmd = new ClassMetaData(new \ReflectionClass($className));

        $rep = new \StdClass();
        $cmd->addRepresentation($rep);
    }

    /**
     * @expectedException \DrestCommon\Representation\RepresentationException
     */
    public function testMetadataRepresentationCanNotBeAnArray()
    {
        $this->setExpectedException('\DrestCommon\Representation\RepresentationException');
        $className = 'DrestTests\\Entities\\NoAnnotation\\User';
        $cmd = new ClassMetaData(new \ReflectionClass($className));

        $rep = array('a', 'b');
        $cmd->addRepresentation($rep);
    }

    /**
     * @expectedException \DrestCommon\Representation\RepresentationException
     */
    public function testMetadataRepresentationCanNotBeAnInteger()
    {
        $this->setExpectedException('\DrestCommon\Representation\RepresentationException');
        $className = 'DrestTests\\Entities\\NoAnnotation\\User';
        $cmd = new ClassMetaData(new \ReflectionClass($className));

        $rep = 1;
        $cmd->addRepresentation($rep);
    }


    public function testMetadataResourceRequiresAtLeastOneServiceDefinition()
    {
        $this->setExpectedException('RuntimeException');
$file_contents = <<<HEREDOC
<?php

\$resources = [];

\$resources['\Entities\User'] = [
    'representations' => ['Json'],
    'routes' => [
    ]
];
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                array($tmp)
            )
        );
        $metadataFactory->getMetadataForClass($tmp);
        
    }

    public function testUnableToLoadMetaDataFromClass()
    {
        $this->setExpectedException('RuntimeException');
$file_contents = <<<HEREDOC
<?php

\$resources = [];

\$resources['\Entities\User'] = [
    
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                array($tmp)
            )
        );
        $metadataFactory->getMetadataForClass($tmp);
        
    }

    public function testDuplicatedRouteName()
    {
        $this->setExpectedException('RuntimeException');
$file_contents = <<<HEREDOC
<?php

\$resources = [];
\$resources['\Entities\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['GET'], 'action' => 'Action\Custom'],
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['GET'], 'action' => 'Action\Custom'],
    ]
];
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                array($tmp)
            )
        );
        $metadataFactory->getMetadataForClass($tmp);
        
    }

    public function testInvalidVerbUsed()
    {
        $this->setExpectedException('RuntimeException');
$file_contents = <<<HEREDOC
<?php

\$resources = [];
\$resources['\Entities\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['DANCE'], 'action' => 'Action\Custom'],
    ]
];
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                array($tmp)
            )
        );
        $metadataFactory->getMetadataForClass($tmp);
        
    }

    public function testEmptyRouteName()
    {

$this->setExpectedException('RuntimeException');
$file_contents = <<<HEREDOC
<?php

\$resources = [];
\$resources['\Entities\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => '', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['DANCE'], 'action' => 'Action\Custom'],
    ]
];
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                array($tmp)
            )
        );
        $metadataFactory->getMetadataForClass($tmp);
        
    }

    public function testHandleAlreadyDefined()
    {

$this->setExpectedException('RuntimeException');
$file_contents = <<<HEREDOC
<?php

\$resources = [];
\$resources['\Entities\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => 'post_user', 'routePattern' => '/user', 'verbs' => ['POST'], 'expose' => ['username', 'email_address', 'profile' => ['firstname', 'lastname'], 'phone_numbers' => ['number']], 'handle_call' => 'populatePost'],
        ['name' => 'update_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['PUT', 'PATCH'], 'expose' => ['email_address', 'profile' => ['firstname', 'lastname']], 'handle_call' => 'populatePost'],
    ]
];
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                array($tmp)
            )
        );
        $metadataFactory->getMetadataForClass($tmp);
        
    }

    public function testGetDefaultOriginRoute()
    {
        include($this->createGoodTmpFile());
        $route = $resources['\Entities\User']['routes'][0];
        $this->assertEquals('get_user', $route['origin']);
    }

    public function testNoRepresentationIsAllowed()
    {
        $this->setExpectedException('RuntimeException');
$file_contents = <<<HEREDOC
<?php

\$resources = [];
\$resources['\Entities\User'] = [
    'routes' => [
        ['name' => 'post_user', 'routePattern' => '/user', 'verbs' => ['POST'], 'expose' => ['username', 'email_address', 'profile' => ['firstname', 'lastname'], 'phone_numbers' => ['number']], 'handle_call' => 'populatePost'],
        ['name' => 'update_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['PUT', 'PATCH'], 'expose' => ['email_address', 'profile' => ['firstname', 'lastname']], 'handle_call' => 'populatePost'],
    ]
];
HEREDOC;
        $tmp = $this->createCustomTmpFile($file_contents);
        $metadataFactory = new MetadataFactory(
            \Drest\Mapping\Driver\PhpDriver::create(
                array($tmp)
            )
        );
        $cmd = $metadataFactory->getMetadataForClass($tmp);

        $this->assertEmpty($cmd->getRepresentations());

    }

    public function createGoodTmpFile() {
$file_contents = <<<HEREDOC
<?php

\$resources = [];

\$resources['\Entities\User'] = [
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

}