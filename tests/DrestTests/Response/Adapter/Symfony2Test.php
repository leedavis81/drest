<?php
namespace DrestTests\Response\Adapter;


use DrestTests\DrestTestCase,
 	Drest\Response,
 	Symfony\Component\HttpFoundation;

class Symfony2Test extends DrestTestCase
{

	/**
	 * Get an instance of the response object with a symfony adapter used
	 * @return Drest\Response;
	 */
	public static function getSymfonyAdapterResponse()
	{
		$symResponse = new HttpFoundation\Response();
		$response = Response::create($symResponse, array('Drest\\Response\\Adapter\\Symfony2'));
		return $response;
	}

	public function testCanSetAndRetrieveHttpHeader()
	{
		$response = self::getSymfonyAdapterResponse();

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
		$response = self::getSymfonyAdapterResponse();

		$document = '<h1>Hello World!&amp;</h1>';
		$response->setBody($document);

		$this->assertEquals($document, $response->getBody());
	}

	public function testCanSetHttpStatusCode()
	{
		$response = self::getSymfonyAdapterResponse();

		$response->setStatusCode(Response::STATUS_CODE_200);

		$this->assertEquals(Response::STATUS_CODE_200, $response->getStatusCode());
	}

	public function testResponseDocumentToString()
	{
	    $httpString = "HTTP/1.0 200 OK\r\nContent-Type: text/html\r\nAccept: application/json\r\n\r\n\r\n<html>\r\n<body>\r\n    This is a test document\r\n</body>\r\n</html>";
		$symResponse = new HttpFoundation\Response($httpString);
		$response = Response::create($symResponse, array('Drest\\Response\\Adapter\\Symfony2'));

		ob_start();
		ob_get_contents();
		echo $response->__toString();
		$actual = ob_get_contents();
		ob_end_clean();
		$this->assertEquals($httpString, $actual);
	}

}