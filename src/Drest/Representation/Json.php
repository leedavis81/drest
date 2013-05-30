<?php
namespace Drest\Representation;

use Drest\DrestException,
    Drest\Response,
    Drest\Request,
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
     * default error reponse document when handling an error
     * @var string $defaultErrorResponseClass
     */
    protected $defaultErrorResponseClass = 'Drest\\Error\\Response\\Json';

    /**
     * The location path of the entity (populated after a post call)
     * @var string $locationPath
     */
    protected $locationPath;

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
    public function toArray($includeKey = true)
    {
	    if (empty($this->data))
	    {
	         throw new \Exception('Json data hasn\'t been loaded. Use either ->write() or ->createFromString() to create it');
	    }

	    $arrayData = json_decode($this->data, true);
	    if (!$includeKey && sizeof($arrayData) == 1 && is_string(key($arrayData)))
	    {
	        return $arrayData[key($arrayData)];
	    }
        return $arrayData;
    }

	/**
	 * (non-PHPdoc)
	 * @see Drest\Representation\InterfaceRepresentation::createFromString($string)
	 */
	public static function createFromString($string)
	{
        $instance = new self();
        $instance->data = $string;
        return $instance;
	}

	/**
	 * (non-PHPdoc)
	 * @see Drest\Representation.InterfaceRepresentation::parseResponse()
	 */
	public function parsePushResponse(Response $response, $verb)
	{
	    switch ($verb)
	    {
	        case Request::METHOD_POST:
                $body = json_decode($response->getBody(), true);
                if (isset($body['response']['location']) && strtolower($body['response']['location']) !== 'unknown')
                {
                    $this->locationPath = implode('/', array_slice(explode('/', $body['response']['location']), 3));
                }
	            break;
            case Request::METHOD_PUT:
            case Request::METHOD_PATCH:
                break;
	    }
	}

    /**
     * Does this representation have loaded location path
     * @return boolean $response
     */
	public function hasLocationPath()
	{
	    return !empty($this->locationPath);
	}

	/**
	 * Get the location path (if it's been loaded)
	 * @return string $location_path
	 */
	public function getLocationPath()
	{
	    if (!empty($this->locationPath))
	    {
	        return $this->locationPath;
	    }
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