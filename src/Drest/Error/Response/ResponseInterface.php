<?php
namespace Drest\Error\Response;


/**
 * Error response document. This information is passed in the body of an errored REST execution
 * @author Lee
 *
 */
interface ResponseInterface
{
    /**
     * Render the error document
     * @return string $response
     */
    public function render();

    /**
     * Set an error message - this can be represented in different ways on the object, but a setter must be present
     * @param string $message
     */
    public function setMessage($message);

    /**
     * Get the content type to be used when then error document is rendered
     * @return string $contentType
     */
    public static function getContentType();

    /**
     * Every error document you should be able to recreate from the generated string
     * @param string $string
     * @return Drest\Error\Response\ResponseInterface $errorResponse
     */
    public static function createFromString($string);
}