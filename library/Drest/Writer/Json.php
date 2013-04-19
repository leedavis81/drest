<?php

namespace Drest\Writer;

use Drest\DrestException;

class Json extends AbstractWriter
{

	/**
	 * @see Drest\Writer\Writer::write()
	 */
	public function write($data)
	{
	    // This abstraction all seems a little pointless considering..
        return json_encode($data);
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