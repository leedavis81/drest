<?php

namespace DrestTests;

/**
 * Base test case class.
 */
use Drest\Configuration,
    Doctrine\ORM;

abstract class DrestTestCase extends \PHPUnit_Framework_TestCase
{


    /**
     * Get a drest manager instance
     * @return Drest\Manager $dm
     */
    public function _getDrestManager(ORM\EntityManager $em = null, $config = null)
    {
        $config = (is_null($config)) ? new Configuration() : $config;
        $em = (is_null($em)) ? $this->_getTestEntityManager() : $em;

    	$dm = \Drest\Manager::create($em, $config);
    	return $dm;
    }

    /**
     * get a test entity manager
     * @param Doctrine\ORM\Configuration $config
     */
    public function _getTestEntityManager(ORM\Configuration $config = null)
    {
        if (is_null($config))
        {
            $config = $this->_getDefaultORMConfig();
        }

        $em = \Doctrine\ORM\EntityManager::create(array(
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
        $ormConfig = new \Doctrine\ORM\Configuration();

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