<?php

namespace Drest\Representation;

use Drest\DrestException,
    Drest\Query\ResultSet;

class Text extends AbstractRepresentation
{
	/**
	 * @see Drest\Representation\InterfaceRepresentation::write()
	 */
	public function write(ResultSet $data)
	{
	    // Echo only the first entry of the result set
	    $data->rewind();
        return $data->current();
	}

    /**
     * Content type to be used when this writer is matched
     * @return string content type
     */
    public function getContentType()
    {
        return 'text/plain';
    }

	public function getMatchableAcceptHeaders()
	{
	    return array();
	}

	public function getMatchableExtensions()
	{
        return array();
	}

	public function getMatchableFormatParams()
	{
        return array();
	}
}