<?php
namespace Drest\Error\Handler;

use Drest\Error\Response;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * The response HTTP status code
     * @var integer $response_code - defaults to 500
     */
    protected $response_code = 500;

    /**
     * Get the response code
     * @return integer $response_code
     */
    final public function getReponseCode()
    {
        return (int) $this->response_code;
    }
}