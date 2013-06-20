<?php
namespace Drest\Error\Handler;

use Drest\Error\Response\ResponseInterface;

interface HandlerInterface
{
    /**
     * Handle an error, passes the error to the respective error response document
     * @param \Exception $e
     * @param integer $defaultResponseCode - the default response code to use if no match on exception type occurs
     * @param ResponseInterface $errorDocument - an error document to be rendered
     */
    public function error(\Exception $e, $defaultResponseCode = 500, ResponseInterface &$errorDocument);
}