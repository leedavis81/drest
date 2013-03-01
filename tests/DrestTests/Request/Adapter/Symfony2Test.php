<?php
namespace DrestTests\Request\Adapter;


use DrestTests\DrestTestCase,
 	Drest\Request\Request,
 	Symfony\Component\HttpFoundation;

class Symfony2Test extends DrestTestCase
{

	/**
	 * Get an instance of the request object with a symfony adapter used
	 * @return Drest\Request\Request;
	 */
	public static function getSymfonyAdapterRequest()
	{
		$symRequest = new HttpFoundation\Request();
		$request = Request::create($symRequest);
		return $request;
	}

	public function testCanSaveAndRetrieveCookie()
	{
		$request = self::getSymfonyAdapterRequest();
		$adapter = $request->getAdapter();

		$cookieName = 'frodo';
		$cookieValue = 'baggins';

		$adapter->setCookie($cookieName, $cookieValue);
		$this->assertNotEmpty($adapter->getCookie());
		$this->assertCount(1, $adapter->getCookie());
		$this->assertEquals($cookieValue, $adapter->getCookie($cookieName));

		$newCookies = array('samwise' => 'gamgee', 'peregrin' => 'took');
		$adapter->setCookie($newCookies);
		$this->assertCount(2, $adapter->getCookie());
		$this->assertEquals($newCookies, $adapter->getCookie());
	}

	public function testCanSaveAndRetrievePostVars()
	{
		$request = self::getSymfonyAdapterRequest();
		$adapter = $request->getAdapter();

		$varName = 'frodo';
		$varValue = 'baggins';

		$adapter->setPost($varName, $varValue);
		$this->assertNotEmpty($adapter->getPost());
		$this->assertCount(1, $adapter->getPost());
		$this->assertEquals($varValue, $adapter->getPost($varName));

		$newValues = array('samwise' => 'gamgee', 'peregrin' => 'took');
		$adapter->setPost($newValues);
		$this->assertCount(2, $adapter->getPost());
	}

	public function testCanSaveAndRetrieveQueryVars()
	{
		$request = self::getSymfonyAdapterRequest();
		$adapter = $request->getAdapter();

		$varName = 'frodo';
		$varValue = 'baggins';

		$adapter->setQuery($varName, $varValue);
		$this->assertNotEmpty($adapter->getQuery());
		$this->assertCount(1, $adapter->getQuery());
		$this->assertEquals($varValue, $adapter->getQuery($varName));

		$newValues = array('samwise' => 'gamgee', 'peregrin' => 'took');
		$adapter->setQuery($newValues);
		$this->assertCount(2, $adapter->getQuery());
	}

	public function testCanSaveAndRetrieveHeaderVars()
	{
		$request = self::getSymfonyAdapterRequest();
		$adapter = $request->getAdapter();

		$varName = 'frodo';
		$varValue = 'baggins';

		$adapter->setHeader($varName, $varValue);
		$this->assertNotEmpty($adapter->getHeaders());
		$this->assertCount(1, $adapter->getHeaders());
		$this->assertEquals($varValue, $adapter->getHeaders($varName));

		$newValues = array('samwise' => 'gamgee', 'peregrin' => 'took');
		$adapter->setHeaders($newValues);
		$this->assertCount(2, $adapter->getHeaders());
	}

	public function testCanSaveCombinedParamTypes()
	{
		$request = self::getSymfonyAdapterRequest();
		$adapter = $request->getAdapter();

		$varName1 = 'frodo';
		$varValue1 = 'baggins';
		$adapter->setCookie($varName1, $varValue1);
		$varName2 = 'samwise';
		$varValue2 = 'gamgee';
		$adapter->setPost($varName2, $varValue2);
		$varName3 = 'peregrin';
		$varValue3 = 'took';
		$adapter->setQuery($varName3, $varValue3);
		$this->assertCount(3, $adapter->getParams());
		$this->assertArrayHasKey($varName2, $adapter->getParams());
		$varName4 = 'peregrin';
		$varValue4 = 'peanut';
		$adapter->setQuery($varName4, $varValue4);
		$this->assertCount(3, $adapter->getParams());
	}

}