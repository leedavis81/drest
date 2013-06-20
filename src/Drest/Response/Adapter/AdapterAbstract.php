<?php

namespace Drest\Response\Adapter;

abstract class AdapterAbstract implements AdapterInterface
{

    /**
     * Abstracted response object (could be zf / symfony object)
     * @var object $response
     */
    protected $response;

    /**
     * Construct an instance of response adapter
     * @param object $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * @see \Drest\Response\Adapter\AdapterInterface::getResponse()
     */
    public function getResponse()
    {
        return $this->response;
    }
}
