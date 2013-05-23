<?php
namespace Drest\Error\Handler;

use Drest\Error\Response\ResponseInterface;

interface HandlerInterface
{
    /**
     * Handle an error, sets the ResultSet to this object
     * @param \Exception $e
     * @param $defaultResponseCode the default response code to use if no match on exception type occurs
     * @param Drest\Error\Response\ResponseInterface $errorDocument an error document to be rendered
     */
    public function error(\Exception $e, $defaultResponseCode = 500, ResponseInterface &$errorDocument);
}