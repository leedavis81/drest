<?php

namespace Drest\Request\Adapter;

class Symfony2Adapter implements Request
{
	/**
	 * Symfony 2 Request object
	 * @var \Symfony\Component\HttpFoundation\Request $request
	 */
	protected $request;


	public function __construct($request)
	{
		$this->request = $request;

	}
}