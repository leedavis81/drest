<?php

namespace DrestTests;

/**
 * Base test case class.
 */
abstract class DrestTestCase extends \PHPUnit_Framework_TestCase
{


	/**
	 *
	 * Setup a test instance of an entity manager
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function setupEntityManager()
	{
		$config = new \Doctrine\ORM\Configuration();

//		$config->setAutoGenerateProxyClasses(true);
//		$config->set

		$em = \Doctrine\ORM\EntityManager::create($conn, $config);
		return $em;
	}
}