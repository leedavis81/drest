<?php

namespace Drest\Writer;

use Drest\DrestException,
    Drest\ResultSet;

class Json extends AbstractWriter
{

	/**
	 * @see Drest\Writer\Writer::write()
	 */
	public function write(ResultSet $data)
	{
	    // This abstraction all seems a little pointless considering..
        return json_encode($data->toArray());
	}

    /**
     * Content type to be used when this writer is matched
     * @return string content type
     */
    public function getContentType()
    {
        return 'application/json';
    }

	public function getMatchableAcceptHeaders()
	{
	    return array(
            'application/json',
            'application/x-javascript',
            'text/javascript',
            'text/x-javascript',
            'text/x-json'
	    );
	}

	public function getMatchableExtensions()
	{
        return array(
        	'json'
        );
	}

	public function getMatchableFormatParams()
	{
        return array(
        	'json'
        );
	}
}