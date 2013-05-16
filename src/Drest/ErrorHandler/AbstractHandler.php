<?php
namespace Drest\ErrorHandler;

use Drest\Query\ResultSet,
    Drest\Response;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * The response HTTP status code
     * @var integer $response_code - defaults to 500
     */
    protected $response_code = 500;

    /**
     * Error result set
     * @var Drest\Query\ResultSet $resultSet
     */
    protected $resultSet;

    /**
     * Get the response code
     * @return integer $response_code
     */
    final public function getReponseCode()
    {
        return (int) $this->response_code;
    }

    /**
     * Set the result set to be passed back to the client.
     * @param Drest\Query\ResultSet $resultSet
     */
    final protected function setResult(ResultSet $resultSet)
    {
        $this->resultSet = $resultSet;
    }

    /**
     * Get the result set that was determined by the error handler
     * @return Drest\Query\ResultSet
     */
    final public function getResultSet()
    {
        if (!$this->resultSet instanceof ResultSet)
        {
            // Default to a blank error
            return ResultSet::create(array(), 'error');
        }
        return $this->resultSet;
    }

}