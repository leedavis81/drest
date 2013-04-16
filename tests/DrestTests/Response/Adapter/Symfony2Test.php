<?php
namespace DrestTests\Response\Adapter;


use DrestTests\DrestTestCase,
 	Drest\Response,
 	Symfony\Component\HttpFoundation;

class Symfony2Test extends DrestTestCase
{

	/**
	 * Get an instance of the response object with a symfony adapter used
	 * @return Drest\Request;
	 */
	public static function getSymfonyAdapterRequest()
	{
		$symResponse = new HttpFoundation\Response();
		$response = Response::create($symResponse);
		return $response;
	}

}