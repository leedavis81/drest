<?php
namespace DrestTests\Request\Adapter;


use DrestTests\DrestTestCase,
 	Drest\Request,
 	Zend\Http,
	\Zend\Http\Header\Cookie;

class ZendFramework2Test extends DrestTestCase
{

	/**
	 * Get an instance of the request object with a symfony adapter used
	 * @return Drest\Request\Request;
	 */
	public static function getZF2AdapterRequest()
	{
		$zfRequest = new Http\Request();
		$request = Request::create($zfRequest);
		return $request;
	}

	public function testCanSaveAndRetrieveHttpVerb()
	{
		$request = self::getZF2AdapterRequest();
		$adapter = $request->getAdapter();

		$method = 'OPTIONS';
		$symRequestObject = $adapter->getRequest();
		$symRequestObject->setMethod($method);

		$this->assertEquals($method, $adapter->getHttpMethod());
	}

	public function testCanSaveAndRetrieveCookie()
	{
		$request = self::getZF2AdapterRequest();
		$adapter = $request->getAdapter();

		$zf2RequestObject = $adapter->getRequest();

		$cookieName = 'frodo';
		$cookieValue = 'baggins';

		$this->markTestIncomplete('Need to create a custom request from header string using \Zend\Http\Request::fromString(....)');

		$zf2RequestObject->getCookie()->$cookieName = $cookieValue;

		$this->assertNotEmpty($adapter->getCookie());
		$this->assertCount(1, $adapter->getCookie());
		$this->assertEquals($cookieValue, $adapter->getCookie($cookieName));

		$newCookies = array('samwise' => 'gamgee', 'peregrin' => 'took');

		$zf2RequestObject->getCookie()->reset();
		$zf2RequestObject->getHeaders()->addHeader(new Cookie($newCookies));

		$this->assertCount(2, $adapter->getCookie());
		$this->assertEquals($newCookies, $adapter->getCookie());
	}

	public function testCanSaveAndRetrievePostVars()
	{
		$request = self::getZF2AdapterRequest();
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
		$request = self::getZF2AdapterRequest();
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
		$request = self::getZF2AdapterRequest();
		$adapter = $request->getAdapter();

		$zf2RequestObject = $adapter->getRequest();

		$varName = 'frodo';
		$varValue = 'baggins';

		$header = new \Zend\Http\Header\GenericHeader($varName, $varValue);
		$zf2RequestObject->getHeaders()->addHeader($header);

		$this->assertNotEmpty($adapter->getHeaders());
		$this->assertCount(1, $adapter->getHeaders());
		$this->assertEquals($varValue, $adapter->getHeaders($varName));

		$newValues = array('samwise' => 'gamgee', 'peregrin' => 'took');
		foreach ($newValues as $headerName => $headerValue)
		{
			$headers[] = new \Zend\Http\Header\GenericHeader($headerName, $headerValue);
		}

		$zf2RequestObject->getHeaders()->clearHeaders();
		$zf2RequestObject->getHeaders()->addHeaders($headers);

		$this->assertCount(2, $adapter->getHeaders());
	}

	public function testCanSaveCombinedParamTypes()
	{
		$request = self::getZF2AdapterRequest();
		$adapter = $request->getAdapter();

		$zf2RequestObject = $adapter->getRequest();

		$this->markTestIncomplete('Need to include cookie value by creating a custom request with header string using \Zend\Http\Request::fromString(....)');
		$varName1 = 'frodo';
		$varValue1 = 'baggins';
		$zf2RequestObject->getCookie()->$varName1 = $varValue1;
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