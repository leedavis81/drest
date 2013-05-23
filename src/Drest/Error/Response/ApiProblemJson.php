<?php
namespace Drest\Error\Response;

use Drest\Query\ResultSet;

/**
 * ApiProblem Document (Json)
 * @author Lee
 */
class ApiProblemJson implements ResponseInterface
{
    protected $describedBy;
    protected $title;
    protected $httpStatus;
    protected $detail;
    protected $supportId;
    protected $more;


    /**
     * (non-PHPdoc)
     * @see Drest\Error\Response.ResponseInterface::setMessage()
     */
    public function setMessage($message)
    {
        $this->title = $message;
    }

	/**
     * @return string $describedBy
     */
    public function getDescribedBy()
    {
        return $this->describedBy;
    }

	/**
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

	/**
     * @return integer $httpStatus
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

	/**
     * @return string $detail
     */
    public function getDetail()
    {
        return $this->detail;
    }

	/**
     * @return integer $supportId
     */
    public function getSupportId()
    {
        return $this->supportId;
    }

    /**
     * @return array $more
     */
    public function getMore()
    {
        return $this->more;
    }

	/**
     * @param string $describedBy
     */
    public function setDescribedBy($describedBy)
    {
        $this->describedBy = $describedBy;
    }

	/**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

	/**
     * @param integer $httpStatus
     */
    public function setHttpStatus($httpStatus)
    {
        $this->httpStatus = $httpStatus;
    }

	/**
     * @param string $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

	/**
     * @param integer $supportId
     */
    public function setSupportId($supportId)
    {
        $this->supportId = $supportId;
    }

    /**
     * Set more information
     * @param array $more
     */
    public function setMore(array $more)
    {
        $this->more = (array) $more;
    }

    /**
     * (non-PHPdoc)
     * @see Drest\Error\Response.ResponseInterface::getContentType()
     */
    public static function getContentType()
    {
        return 'application/api-problem+json';
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

        // @todo: need to recurse $result to populate the instance (or at least the "more" variable)

        //return $instance;
    }

    /**
     * Get the response document representation of this object
     * @return string $response
     */
    public function render()
    {
        return json_encode(
            array_filter(get_object_vars($this), function($item){
                return !empty($item);
            })
        );
    }
}