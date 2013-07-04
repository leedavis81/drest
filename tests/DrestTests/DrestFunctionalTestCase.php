<?php
namespace DrestTests;


abstract class DrestFunctionalTestCase extends DrestTestCase
{

    /**
     * Shared connection
     * @var \Doctrine\DBAL\Connection
     */
    protected static $_sharedConn;

    /**
     * @var \Doctrine\ORM\Tools\SchemaTool
     */
    protected $_schemaTool;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;


    protected function setUp()
    {
        parent::setUp();

        if (!isset(static::$_sharedConn)) {
            static::$_sharedConn = $this->_getDefaultConnection();
        }

        $this->_em = $this->_getTestEntityManager(static::$_sharedConn);
        $this->_schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->_em);

        $this->_schemaTool->createSchema($this->_em->getMetadataFactory()->getAllMetadata());
    }


    protected function tearDown()
    {
        // Clean anything still idle in the UOW
        $this->_em->clear();

        // Clear out anything set in the db (schema is recreated on setUp()
        $this->_schemaTool->dropDatabase();
    }
}