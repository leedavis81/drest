<?php

namespace DrestTests;

use Doctrine\ORM;
use Drest\Configuration;
use Drest\Event\Manager as EventManager;
use Drest\Manager;

/**
 * Base test case class.
 */
abstract class DrestTestCase extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        // Register the DREST annotations
        \Drest\Mapping\Driver\AnnotationDriver::registerAnnotations();


        // Register the ORM annotations
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
            __DIR__ . '/../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
        );
    }

    /**
     * Get a drest manager instance
     * @param  ORM\EntityManager    $em
     * @param  Configuration        $config
     * @param  \Drest\Event\Manager $evm
     * @return Manager              $dm
     */
    public function _getDrestManager(
        ORM\EntityManager $em = null,
        Configuration $config = null,
        EventManager $evm = null
    ) {
        if (is_null($config)) {
            $config = $this->_getDefaultDrestConfig();
        }
        $em = (is_null($em)) ? $this->_getTestEntityManager() : $em;

        $dm = Manager::create($em, $config, $evm);

        return $dm;
    }

    /**
     * get a test entity manager
     * @param  \Doctrine\DBAL\Connection $conn
     * @param  ORM\Configuration         $config
     * @return ORM\EntityManager
     */
    public function _getTestEntityManager(\Doctrine\DBAL\Connection $conn = null, ORM\Configuration $config = null)
    {
        if (is_null($config)) {
            $config = $this->_getDefaultORMConfig();
        }

        if (is_null($conn)) {
            $conn = $this->_getDefaultConnection();
        }
        $em = ORM\EntityManager::create($conn, $config);

        return $em;
    }

    public function _getDefaultConnection()
    {
        $params = array(
            'driver' => 'pdo_sqlite',
            'memory' => true
        );

        return \Doctrine\DBAL\DriverManager::getConnection($params);
    }

    public function _getDefaultDrestConfig()
    {
        $config = new Configuration();
        $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
        $config->addPathsToConfigFiles(array(__DIR__ . '/Entities'));
        $config->setDebugMode(true);

        return $config;
    }

    public function _getDefaultORMConfig()
    {
        $ormConfig = new ORM\Configuration();

        $pathToEntities = array(__DIR__ . '/Entities');
        $ORMDriver = $ormConfig->newDefaultAnnotationDriver($pathToEntities, false);
        $ormConfig->setMetadataDriverImpl($ORMDriver);

        // Do proxy stuff
        $ormConfig->setProxyDir(__DIR__ . '/Entities/Proxies');
        $ormConfig->setProxyNamespace('DrestTests\Entities\Proxies');
        $ormConfig->setAutoGenerateProxyClasses(true);

        return $ormConfig;
    }
}
