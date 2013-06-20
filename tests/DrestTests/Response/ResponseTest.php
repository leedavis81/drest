<?php
namespace DrestTests\Response;


use Drest\Response;
use DrestTests\DrestTestCase;

class ResponseTest extends DrestTestCase
{

    public function testCreateResponse()
    {
        $response = new Response();

        // Ensure default response object creates a symfony response
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response->getResponse());
    }

    public function testStaticCreateResponse()
    {
        $response = Response::create();

        // Ensure default response object creates a symfony response
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response->getResponse());
    }
}

