<?php

namespace Drest\Response\Adapter;

abstract class AdapterAbstract implements AdapterInterface
{

	/**
	 * Abstracted reponse object (could be zf / symfony object)
	 * @var object $response
	 */
	protected $response;

	/**
	 * Construct an instance of response adapter
	 * @param object $reponse
	 */
	public function __construct($response)
	{
		$this->response = $response;
	}
}
