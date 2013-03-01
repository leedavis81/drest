<?php

namespace Drest\Request\Adapter;

abstract class AdapterAbstract implements AdapterInterface
{

	/**
	 * Abstracted request object (could be zf / symfony object)
	 * @var object $request
	 */
	protected $request;

	/**
	 * Construct an instance of request adapter
	 * @param object $request
	 */
	public function __construct($request)
	{
		$this->request = $request;
	}

	/**
	 * @see Drest\Request\Adapter.Request::getParams()
	 */
	public function getParams()
	{
		return array_merge($this->getCookie(), $this->getPost(), $this->getQuery());
	}
}