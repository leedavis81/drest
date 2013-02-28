<?php

namespace Drest\Request\Adapter;

class ZendFramework2Adapter implements Request
{
	/**
	 * ZF 2 Request object
	 * @var /Zend/Http/Request $request
	 */
	protected $request;


	public function __construct($request)
	{
		$this->request = $request;

	}
}



