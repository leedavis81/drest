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

	}

	public function getMatchableExtensions()
	{

	}

	public function getMatchableFormatParam()
	{

	}
}