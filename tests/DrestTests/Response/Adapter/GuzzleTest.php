<?php
namespace DrestTests\Response\Adapter;

use Drest\Response;
use DrestTests\DrestTestCase;
use Guzzle\Http\Message\Response as GuzzleResponse;

class GuzzleTest extends DrestTestCase
{

    /**
     * Get an instance of the response object with a symfony adapter used
     * @return Response;
     */
    public static function getGuzzleAdapterResponse()
    {
        $guzResponse = new GuzzleResponse(200);
        $response = Response::create($guzResponse, array('Drest\\Response\\Adapter\\Guzzle'));
        return $response;
    }

    public function testCanSetAndRetrieveHttpHeader()
    {
        $response = self::getGuzzleAdapterResponse();

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
        $response = self::getGuzzleAdapterResponse();

        $document = '<h1>Hello World!&amp;</h1>';
        $response->setBody($document);

        $this->assertEquals($document, $response->getBody());
    }

    public function testCanSetHttpStatusCode()
    {
        $response = self::getGuzzleAdapterResponse();

        $response->setStatusCode(Response::STATUS_CODE_200);

        $this->assertEquals(Response::STATUS_CODE_200, $response->getStatusCode());
    }
    /*
    public function testResponseDocumentToString()
    {
        $this->markTestSkipped('Guzzle will automatically use HTTP 1.1 and add additional header params (Content-Length). Use a custom test until this changes');

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
        $guzzResponse = GuzzleResponse::fromMessage($httpString);
        $response = Response::create($guzzResponse, array('Drest\\Response\\Adapter\\Guzzle'));

        ob_start();
        ob_get_contents();
        echo $response->__toString();
        $actual = ob_get_contents();
        ob_end_clean();
        $this->assertEquals($httpString, $actual);
    }

    public function testCustomResponseDocumentToString()
    {
        $this->markTestSkipped('Unable to do a custom response document test as content-length varies based on OS used due to carrage returns');
        $httpString = <<<EOT
HTTP/1.1 200 OK
Content-Type: text/html
Accept: application/json
Content-Length: ???

<html>
<body>
    This is a test document
</body>
</html>
EOT;
        $guzzResponse = GuzzleResponse::fromMessage($httpString);
        $response = Response::create($guzzResponse, array('Drest\\Response\\Adapter\\Guzzle'));

        ob_start();
        ob_get_contents();
        echo $response->__toString();
        $actual = ob_get_contents();
        ob_end_clean();
        $this->assertEquals($httpString, $actual);
    }
    */
}