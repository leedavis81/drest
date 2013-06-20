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
     * @return string $message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @see \Drest\Error\Response\ResponseInterface::render()
     */
    public function render()
    {
        return 'error: ' . $this->message;
    }

    /**
     * @see \Drest\Error\Response\ResponseInterface::getContentType()
     */
    public static function getContentType()
    {
        return 'text/plain';
    }

    /**
     * recreate this error document from a generated string
     * @param string $string
     * @return Xml $errorResponse
     */
    public static function createFromString($string)
    {
        $instance = new self();
        $parts = explode(':', $string);
        $instance->setMessage($parts[1]);
        return $instance;
    }
}