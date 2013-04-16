<?php
namespace DrestTests\Request;


use DrestTests\DrestTestCase,
 	Drest\Request,
 	Symfony\Component\HttpFoundation,
 	Zend\Http;

class RequestTest extends DrestTestCase
{

	public function testCreateRequest()
	{
		$request = new Request();

		// Ensure default request object creates a symfony request
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $request->getAdapter()->getRequest());
	}

	public function testStaticCreateRequest()
	{
		$request = Request::create();
	}

	public function testCreateRequestWithZendFramework2RequestObject()
	{
		$zfRequest = new Http\Request();
		$request = Request::create($zfRequest);

		// Ensure request object creates a zf2 request
		$this->assertInstanceOf('Zend\Http\Request', $request->getAdapter()->getRequest());
	}

	public function testCreateRequestWithSymfony2RequestObject()
	{
		$symRequest = new HttpFoundation\Request();
		$request = Request::create($symRequest);

		// Ensure request object creates a symfony2 request
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $request->getAdapter()->getRequest());
	}
}

