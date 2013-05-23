<?php
namespace Drest\Error\Response;

/**
 * ApiProblem Document (Json)
 * @author Lee
 */
class Text implements ResponseInterface
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
        return 'error: ' . $this->message;
    }

    /**
     * (non-PHPdoc)
     * @see Drest\Error\Response.ResponseInterface::getContentType()
     */
    public static function getContentType()
    {
        return 'text/plain';
    }


    /**
     * recreate this error document from a generated string
     * @param string $string
     * @return Drest\Error\Response\Xml $errorResponse
     */
    public static function createFromString($string)
    {
        $instance = new self();
        $parts = explode(':', $string);
        $instance->setMessage($parts[1]);
        return $instance;
    }
}