<?php

namespace Drest\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class DrestRepository
	extends EntityRepository
	implements Repository
{

	/**
	 * Drest Manager Object
	 * @var Drest\Manager $dm
	 */
	protected $_dm;


	public function getItem()
	{
		$this->find($id);
	}

	public function getCollection()
	{

	}

	public function putItem()
	{

	}

	public function putCollection()
	{

	}

	public function deleteItem()
	{

	}

	public function deleteCollection()
	{

	}

	public function postItem()
	{

	}

	public function postCollection()
	{

	}

}