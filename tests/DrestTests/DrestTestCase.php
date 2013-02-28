<?php

namespace DrestTests;

/**
 * Base test case class.
 */
class DrestTestCase extends \PHPUnit_Framework_TestCase
{


	/**
	 *
	 * Setup a test instance of an entity manager
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function setupEntityManager()
	{
		$em = \Doctrine\ORM\EntityManager::create($conn, $config);
		return $em;
	}
}