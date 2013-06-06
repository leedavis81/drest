<?php
namespace Drest\Error;

use Drest\Error\Response\ResponseInterface,
    Drest\Response;

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
     * @var Drest\Response $response
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
     * @param Drest\Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Get the errored request's response
     * @return Drest\Response $response
     */
    public function getResponse()
    {
        return $this->response;
    }
}