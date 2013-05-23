<?php
namespace Drest\Response;

use Drest\Error\Response\ResponseInterface,
    Guzzle\Http\Message\Response;

/**
 *
 * Error Exception - sent from Drest\Client when an error is returned from a drest endpoint
 * @author Lee
 */
class ErrorException extends \Exception
{

    /**
     * An error document
     * @var Drest\Error\Response\ResponseInterface $errorDocument
     */
    public $errorDocument;

    /**
     * The response document
     * @var Guzzle\Http\Message\Response $response
     */
    public $response;

    /**
     *
     * Set the error document
     * @param Drest\Error\Response\ResponseInterface $errorDocument
     */
    public function setErrorDocument(ResponseInterface $errorDocument)
    {
        $this->errorDocument = $errorDocument;
    }

    /**
     * Get the error document
     * @return Drest\Error\Response\ResponseInterface $errorDocument
     */
    public function getErrorDocument()
    {
        return $this->errorDocument;
    }

    /**
     * Set the response of the error request
     * @param Guzzle\Http\Message\Response $response
     */
    public function setResponse(Response $response)
    {
        return $this->response;
    }

    /**
     * Get the errored request's response
     * @return Guzzle\Http\Message\Response $response
     */
    public function getResponse()
    {
        return $this->response;
    }
}