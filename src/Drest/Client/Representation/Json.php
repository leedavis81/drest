<?php
namespace Drest\Client\Representation;

class Json extends AbstractRepresentation
{

    // STATE_DELETE, NEEDS_SYNC
    protected $state;

    protected $response = '';

    // Parse the body of the document and load up the parent object
    public function parse();
}