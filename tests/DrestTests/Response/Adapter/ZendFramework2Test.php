<?php
namespace DrestTests\Response\Adapter;


use DrestTests\DrestTestCase,
 	Drest\Response,
 	Zend\Http;

class ZendFramework2Test extends DrestTestCase
{

	/**
	 * Get an instance of the response object with a ZF2 adapter used
	 * @return Drest\Response;
	 */
	public static function getZF2AdapterResponse()
	{
		$zf2Response = new Http\Response();
		$response = Response::create($zf2Response, array('Drest\\Response\\Adapter\\ZendFramework2'));
		return $response;
	}

	public function testMe()
	{
	    $this->assertTrue(true);
	}


	public function testCanSetAndRetrieveHttpHeader()
	{
		$response = self::getZF2AdapterResponse();

		$varName = 'frodo';
		$varValue = 'baggins';

		// Clear out anything already set (some fw's populate defaults)
		$response->setHttpHeader(array());

		$this->assertCount(0, $response->getHttpHeader());

		$response->setHttpHeader($varName, $varValue);

        $this->assertNotEmpty($response->getHttpHeader($varName));
        $this->assertCount(1, $response->getHttpHeader());
        $this->assertEquals($varValue, $response->getHttpHeader($varName));

        // Single valued entries should be converted into arrays
		$singleValues = array('samwise' => 'gamgee', 'peregrin' => 'took');
		$response->setHttpHeader($singleValues);

		$this->assertCount(2, $response->getHttpHeader());
		$this->assertEquals($singleValues, $response->getHttpHeader());

        // Test multi-valued entries
		$multiArrayedValues = array('samwise' => array('gamgee', 'gamgee2'), 'peregrin' => array('took', 'took2'));
		$multiArrayedValuesReponse = array('samwise' => 'gamgee, gamgee2', 'peregrin' => 'took, took2');
		$response->setHttpHeader($multiArrayedValues);

		$this->assertCount(2, $response->getHttpHeader());
		$this->assertEquals($multiArrayedValuesReponse, $response->getHttpHeader());
	}


	public function testCanSetAndRetrieveBody()
	{
		$response = self::getZF2AdapterResponse();

		$document = '<h1>Hello World!&amp;</h1>';
		$response->setBody($document);

		$this->assertEquals($document, $response->getBody());
	}

	public function testCanSetHttpStatusCode()
	{
		$response = self::getZF2AdapterResponse();

		$response->setStatusCode(Response::STATUS_CODE_200);

		$this->assertEquals(Response::STATUS_CODE_200, $response->getStatusCode());
	}

	public function testResponseDocumentToString()
	{
	    $httpString = <<<EOT
HTTP/1.0 200 OK
Content-Type: text/html
Accept: application/json

<html>
<body>
    This is a test document
</body>
</html>
EOT;
		$zf2Response = Http\Response::fromString($httpString);
		$response = Response::create($zf2Response, array('Drest\\Response\\Adapter\\ZendFramework2'));

		ob_start();
		ob_get_contents();
		echo $response->__toString();
		$actual = ob_get_contents();
		ob_end_clean();
		$this->assertEquals($httpString, $actual);
	}

}