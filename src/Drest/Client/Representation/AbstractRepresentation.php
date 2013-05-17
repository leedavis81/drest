<?php
namespace Drest\Client\Representation;

class AbstractRepresentation
{

    const STATE_UPDATED = 1;
    const STATE_DELETE = 2;

    // STATE_DELETE, NEEDS_SYNC
    protected $state;

    /**
     * Raw data loaded from previous call
     * @var string
     */
    protected $data;


    protected function setState()
    {

    }

}