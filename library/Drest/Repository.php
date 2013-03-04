<?php

namespace Drest;

use Doctrine\ORM\EntityRepository,
	Drest\Repository\DefaultRepository;

class Repository extends EntityRepository
{

	/**
	 * Drest request object, comprised of an adapter
	 * @var \Drest\Request $request
	 */
	protected $request;



	public function getItem()
	{
		return DefaultRepository::getItem($this);
	}

	public function getCollection()
	{
		return DefaultRepository::getCollection($this);
	}


	/**
	 * @todo: implement other default return methods
	 */

	/**
	 * @todo: implement this
	 * Echo's the clients request directly back to them (no entity data is used)
	 */
	protected function traceRequest()
	{

	}

}