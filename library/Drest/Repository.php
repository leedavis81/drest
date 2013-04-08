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
	 * Inject the request object into the repository
	 * @param Drest\Request $request
	 */
	public function setRequest(Request $request)
	{
	    $this->request = $request;
	}

	/**
	 * Returns the request adapter object
	 * @return Drest\Request\Adapter\AdapterAbstract $requestAdapter
	 */
	public function getRequestAdapter()
	{
        return $this->request->getAdapter();
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