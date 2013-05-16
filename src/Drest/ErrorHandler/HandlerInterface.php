<?php
namespace Drest\ErrorHandler;


interface HandlerInterface
{
    /**
     * Handle an error, sets the ResultSet to this object
     * @param \Exception $e
     * @param $defaultResponseCode the default response code to use if no match on exception type occurs
     */
    public function error(\Exception $e, $defaultResponseCode = 500);
}