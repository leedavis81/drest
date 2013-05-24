<?php
namespace Drest\Error\Response;

/**
 * ApiProblem Document (Json)
 * @author Lee
 */
class Json implements ResponseInterface
{
    /**
     * The error message
     * @var string $message
     */
    public $message;

    /**
     * (non-PHPdoc)
     * @see Drest\Error\Response.ResponseInterface::setMessage()
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return the $message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * (non-PHPdoc)
     * @see Drest\Error\Response.ResponseInterface::render()
     */
	public function render()
    {
        return json_encode(
            array('error' => $this->message)
        );
    }

    /**
     * (non-PHPdoc)
     * @see Drest\Error\Response.ResponseInterface::getContentType()
     */
    public static function getContentType()
    {
        return 'application/json';
    }

    /**
     * Every error document you should be able to recreate from the generated string
     * @param string $string
     * @return Drest\Error\Response\Json $errorResponse
     */
    public static function createFromString($string)
    {
        $result = json_decode($string, true);
        $instance = new self();
        if (isset($result['error']))
        {
            $instance->setMessage($result['error']);
        }
        return $instance;
    }
}