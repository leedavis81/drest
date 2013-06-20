<?php
namespace Drest\Error\Handler;

use Drest\Error\Response\ResponseInterface;
use Drest\Response;

/**
 * Default error handler class
 * These can be customised by creating your own handler that extends the AbstractHandler class
 * These should only be provided with the failure exception
 * @author Lee
 *
 */
class DefaultHandler extends AbstractHandler
{

    public function error(\Exception $e, $defaultResponseCode = 500, ResponseInterface &$errorDocument)
    {
        switch (get_class($e)) {
            /**
             * results exceptions
             * ORM\NonUniqueResultException
             * ORM\NoResultException
             * ORM\OptimisticLockException
             * ORM\PessimisticLockException
             * ORM\TransactionRequiredException
             * ORM\UnexpectedResultException
             */
            case 'Doctrine\ORM\NonUniqueResultException':
                $this->response_code = Response::STATUS_CODE_300;
                $error_message = 'Multiple resources available';
                break;
            case 'Doctrine\ORM\NoResultException':
                $this->response_code = Response::STATUS_CODE_404;
                $error_message = 'No resource available';
                break;
            /**
             * configuration / request exception
             * Drest\Route\MultipleRoutesException
             */
            case 'Drest\Query\InvalidExposeFieldsException':
                $this->response_code = Response::STATUS_CODE_400;
                $error_message = $e->getMessage();
                break;
            case 'Drest\Route\NoMatchException':
                $this->response_code = Response::STATUS_CODE_404;
                $error_message = $e->getMessage();
                break;
            case 'Drest\Representation\UnableToMatchRepresentationException';
                $this->response_code = Response::STATUS_CODE_415;
                $error_message = 'Requested media type is not supported';
                break;
            default:
                $error_message = 'An unknown error occured';
                $this->response_code = $defaultResponseCode;
                break;
        }
        $errorDocument->setMessage($error_message);
    }
}