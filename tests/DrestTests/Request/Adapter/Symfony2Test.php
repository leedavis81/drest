<?php
namespace DrestTests\Request\Adapter;


use DrestTests\DrestTestCase,
    Drest\Request,
    Symfony\Component\HttpFoundation;

class Symfony2Test extends DrestTestCase
{

    /**
     * Get an instance of the request object with a symfony adapter used
     * @return Request;
     */
    public static function getSymfonyAdapterRequest()
    {
        $symRequest = new HttpFoundation\Request();
        $request = Request::create($symRequest, array('Drest\\Request\\Adapter\\Symfony2'));

        return $request;
    }

    public function testCanSaveAndRetrieveHttpVerb()
    {
        $request = self::getSymfonyAdapterRequest();

        $method = 'POST';
        $symRequestObject = $request->getRequest();
        $symRequestObject->setMethod($method);

        $this->assertEquals($method, $request->getHttpMethod());
    }

    public function testCanSaveAndRetrieveCookie()
    {
        $request = self::getSymfonyAdapterRequest();

        $cookieName = 'frodo';
        $cookieValue = 'baggins';

        $symRequestObject = $request->getRequest();
        $symRequestObject->cookies->set($cookieName, $cookieValue);

        $this->assertNotEmpty($request->getCookie());
        $this->assertCount(1, $request->getCookie());
        $this->assertEquals($cookieValue, $request->getCookie($cookieName));

        $newCookies = array('samwise' => 'gamgee', 'peregrin' => 'took');
        $symRequestObject->cookies->replace($newCookies);

        $this->assertCount(2, $request->getCookie());
        $this->assertEquals($newCookies, $request->getCookie());
    }

    public function testCanSaveAndRetrievePostVars()
    {
        $request = self::getSymfonyAdapterRequest();

        $varName = 'frodo';
        $varValue = 'baggins';

        $request->setPost($varName, $varValue);
        $this->assertNotEmpty($request->getPost());
        $this->assertCount(1, $request->getPost());
        $this->assertEquals($varValue, $request->getPost($varName));

        $newValues = array('samwise' => 'gamgee', 'peregrin' => 'took');
        $request->setPost($newValues);
        $this->assertCount(2, $request->getPost());
    }

    public function testCanSaveAndRetrieveQueryVars()
    {
        $request = self::getSymfonyAdapterRequest();

        $varName = 'frodo';
        $varValue = 'baggins';

        $request->setQuery($varName, $varValue);
        $this->assertNotEmpty($request->getQuery());
        $this->assertCount(1, $request->getQuery());
        $this->assertEquals($varValue, $request->getQuery($varName));

        $newValues = array('samwise' => 'gamgee', 'peregrin' => 'took');
        $request->setQuery($newValues);
        $this->assertCount(2, $request->getQuery());
    }


    public function testCanSaveAndRetrieveHeaderVars()
    {
        $request = self::getSymfonyAdapterRequest();

        $varName = 'frodo';
        $varValue = 'baggins';

        $symRequestObject = $request->getRequest();
        $symRequestObject->headers->set($varName, $varValue);

        $this->assertNotEmpty($request->getHeaders());
        $this->assertCount(1, $request->getHeaders());
        $this->assertEquals($varValue, $request->getHeaders($varName));

        $newValues = array('samwise' => 'gamgee', 'peregrin' => 'took');
        $symRequestObject->headers->replace($newValues);

        $this->assertCount(2, $request->getHeaders());
    }

    public function testCanSaveCombinedParamTypes()
    {
        $request = self::getSymfonyAdapterRequest();

        $symRequestObject = $request->getRequest();

        $varName1 = 'frodo';
        $varValue1 = 'baggins';
        $symRequestObject->cookies->set($varName1, $varValue1);
        $varName2 = 'samwise';
        $varValue2 = 'gamgee';
        $request->setPost($varName2, $varValue2);
        $varName3 = 'peregrin';
        $varValue3 = 'took';
        $request->setQuery($varName3, $varValue3);
        $this->assertCount(3, $request->getParams());
        $this->assertArrayHasKey($varName2, $request->getParams());
        $varName4 = 'peregrin';
        $varValue4 = 'peanut';
        $request->setQuery($varName4, $varValue4);
        $this->assertCount(3, $request->getParams());
    }

}