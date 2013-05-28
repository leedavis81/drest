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

    protected $defaultErrorResponseClass = 'Drest\\Error\\Response\\Json';

	/**
	 * (non-PHPdoc)
	 * @see Drest\Representation\InterfaceRepresentation::write()
	 */
	public function write(ResultSet $data)
	{
	    $this->data = json_encode($data->toArray());
	}

    /**
     * (non-PHPdoc)
     * @see Drest\Representation.InterfaceRepresentation::toArray()
     */
    public function toArray()
    {
	    if (empty($this->data))
	    {
	         throw new \Exception('Json data hasn\'t been loaded. Use either ->write() or ->createFromString() to create it');
	    }
        return $this->data;
    }

	/**
	 * (non-PHPdoc)
	 * @see Drest\Representation\InterfaceRepresentation::createFromString($string)
	 */
	public static function createFromString($string)
	{
        $result = json_decode($string, true);
        $instance = new self();
        $instance->data = $result;
        return $instance;
	}

    /**
     * Content type to be used when this writer is matched
     * @return string content type
     */
    public function getContentType()
    {
        return 'application/json';
    }

    /**
     * (non-PHPdoc)
     * @see Drest\Representation.InterfaceRepresentation::getMatchableAcceptHeaders()
     */
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

	/**
	 * (non-PHPdoc)
	 * @see Drest\Representation.InterfaceRepresentation::getMatchableExtensions()
	 */
	public function getMatchableExtensions()
	{
        return array(
        	'json'
        );
	}

	/**
	 * (non-PHPdoc)
	 * @see Drest\Representation.InterfaceRepresentation::getMatchableFormatParams()
	 */
	public function getMatchableFormatParams()
	{
        return array(
        	'json'
        );
	}
}