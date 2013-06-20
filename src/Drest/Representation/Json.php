<?php
namespace Drest\Representation;

use Drest\DrestException;
use Drest\Query\ResultSet;
use Drest\Request;
use Drest\Response;

/**
 *
 * Server implementation of the JSON representation
 * @author Lee
 *
 */
class Json extends AbstractRepresentation
{

    /**
     * default error response document when handling an error
     * @var string $defaultErrorResponseClass
     */
    protected $defaultErrorResponseClass = 'Drest\\Error\\Response\\Json';

    /**
     * @see \Drest\Representation\InterfaceRepresentation::write()
     */
    public function write(ResultSet $data)
    {
        $this->data = json_encode($data->toArray());
    }

    /**
     * @see \Drest\Representation\InterfaceRepresentation::toArray()
     */
    public function toArray($includeKey = true)
    {
        if (empty($this->data)) {
            throw new \Exception('Json data hasn\'t been loaded. Use either ->write() or ->createFromString() to create it');
        }

        $arrayData = json_decode($this->data, true);
        if (!$includeKey && sizeof($arrayData) == 1 && is_string(key($arrayData))) {
            return $arrayData[key($arrayData)];
        }
        return $arrayData;
    }

    /**
     * @see Drest\Representation\InterfaceRepresentation::createFromString($string)
     */
    public static function createFromString($string)
    {
        $instance = new self();
        $instance->data = $string;
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
     * @see \Drest\Representation\InterfaceRepresentation::getMatchableAcceptHeaders()
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
     * @see \Drest\Representation\InterfaceRepresentation::getMatchableExtensions()
     */
    public function getMatchableExtensions()
    {
        return array(
            'json'
        );
    }

    /**
     * @see \Drest\Representation\InterfaceRepresentation::getMatchableFormatParams()
     */
    public function getMatchableFormatParams()
    {
        return array(
            'json'
        );
    }
}