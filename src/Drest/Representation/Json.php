<?php
namespace Drest\Representation;

use Drest\DrestException,
    Drest\Query\ResultSet;

/**
 *
 * Server implementation of the JSON representation
 * @author Lee
 *
 */
class Json extends AbstractRepresentation
{
	/**
	 * @see Drest\Representation\InterfaceRepresentation::write()
	 */
	public function write(ResultSet $data)
	{
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