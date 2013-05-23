<?php

namespace Drest\Representation;

use Drest\DrestException,
    Drest\Query\ResultSet;

/**
 * XML Conversion inspired from http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * @author Lee
 */
class Xml extends AbstractRepresentation
{

    protected $defaultErrorResponseClass = 'Drest\\Error\\Response\\Xml';

    /**
     * DOM document
     * @var DomDocument $xml
     */
    protected $xml;

    /**
     * Name of the current node being parsed
     * @param string $current_node_name
     */
    protected $current_node_name;

    /**
     * Content type to be used when this writer is matched
     * @return string content type
     */
    public function getContentType()
    {
        return 'application/xml';
    }

	public function getMatchableAcceptHeaders()
	{
        return array(
            'application/xml',
            'text/xml'
        );
	}

	public function getMatchableExtensions()
	{
        return array(
        	'xml'
        );
	}

	public function getMatchableFormatParams()
	{
        return array(
        	'xml'
        );
	}

	/**
	 * @see Drest\Writer\Writer::write()
	 */
	public function write(ResultSet $data)
	{
	    $this->xml =  new \DomDocument('1.0', 'UTF-8');
	    $this->xml->formatOutput = true;

	    $dataArray = $data->toArray();
        $this->xml->appendChild($this->convert(key($dataArray), $dataArray[key($dataArray)]));
	    $this->data = $this->xml->saveXML();
	}

    /**
     * Convert an Array to XML
     * @param string $root_node - name of the root node to be converted
     * @param array $data - aray to be converterd
     * @return DOMNode
     */
    protected function convert($root_node, $data = array())
    {
        if(!$this->isValidTagName($root_node))
        {
            throw new \Exception('Array to XML Conversion - Illegal character in element name: '. $root_node);
        }

        $node = $this->xml->createElement($root_node);

        if(is_array($data))
        {
            foreach($data as $key => $value)
            {
                $this->current_node_name = $root_node;
                $key = (is_numeric($key)) ? \Drest\Inflector::singularize($this->current_node_name) : $key;
                $node->appendChild($this->convert($key, $value));
                unset($data[$key]);
            }
        } else
        {
            $node->appendChild($this->xml->createTextNode($this->bool2str($data)));
        }

        return $node;
    }

    /**
     * Get string representation of boolean value
     */
    protected function bool2str($v)
    {
        if (is_bool($v))
        {
            return ($v) ? 'true' : 'false';
        }
        return $v;
    }

    /**
     * Check if the tag name or attribute name contains illegal characters
     * Ref: http://www.w3.org/TR/xml/#sec-common-syn
     */
    protected function isValidTagName($tag)
    {
        try
        {
            new \DOMElement(':'.$tag);
            return true;
        } catch (\DOMException $e) {
            return false;
        }
    }
}