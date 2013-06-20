<?php
namespace Drest\Client;

use Drest\Representation\AbstractRepresentation;
use Drest\Response as DrestResponse;

class Response extends DrestResponse
{
    /**
     * Representation object
     * @var \Drest\Representation\AbstractRepresentation
     */
    protected $representation;

    /**
     * Create an instance of client response and wrap the representation object
     */
    public function __construct(AbstractRepresentation $representation, $response_object = null)
    {
        $this->representation = $representation;

        parent::__construct($response_object);
    }

    /**
     * Get the responded representation object
     * @return \Drest\Representation\AbstractRepresentation
     */
    public function getRepresentation()
    {
        return $this->representation;
    }
}
