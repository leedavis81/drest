<?php

namespace DrestTests;

use Doctrine\ORM;
use Drest\Configuration;
use Drest\Manager;

/**
 * Base test case class.
 */
abstract class DrestTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Get a drest manager instance
     * @param ORM\EntityManager $em
     * @param Configuration $config
     * @return Manager $dm
     */
    public function _getDrestManager(ORM\EntityManager $em = null, Configuration $config = null)
    {
        $config = (is_null($config)) ? new Configuration() : $config;
        $em = (is_null($em)) ? $this->_getTestEntityManager() : $em;

        $dm = Manager::create($em, $config);
        return $dm;
    }

    /**
     * get a test entity manager
     * @param ORM\Configuration $config
     * @return ORM\EntityManager
     */
    public function _getTestEntityManager(ORM\Configuration $config = null)
    {
        if (is_null($config)) {
            $config = $this->_getDefaultORMConfig();
        }

        $em = ORM\EntityManager::create(array(
            'host' => 'localhost',
            'user' => 'developer',
            'password' => 'developer',
            'dbname' => 'drest',
            'driver' => 'pdo_sqlite'
        ), $config);

        return $em;
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