<?php
namespace Drest\Error;

use Drest\Error\Response\ResponseInterface;
use Drest\Response;

/**
 *
 * Error Exception - sent from Drest\Client when an error is returned from a drest endpoint
 * @author Lee
 */
class ErrorException extends \Exception
{

    /**
     * An error document
     * @var ResponseInterface $errorDocument
     */
    public $errorDocument;

    /**
     * The response document
     * @var Response $response
     */
    public $response;

    /**
     *
     * Set the error document
     * @param ResponseInterface $errorDocument
     */
    public function setErrorDocument(ResponseInterface $errorDocument)
    {
        $this->errorDocument = $errorDocument;
    }

    /**
     * Get the error document
     * @return ResponseInterface $errorDocument
     */
    public function getErrorDocument()
    {
        return $this->errorDocument;
    }

    /**
     * Set the response of the error request
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Get the error'd request's response
     * @return Response $response
     */
    public function getResponse()
    {
        return $this->response;
    }
}