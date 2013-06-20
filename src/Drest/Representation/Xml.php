<?php

namespace Drest\Representation;

use Drest\DrestException;
use Drest\Inflector;
use Drest\Query\ResultSet;
use Drest\Request;
use Drest\Response;

/**
 * XML Conversion inspired from http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * @author Lee
 */
class Xml extends AbstractRepresentation
{

    protected $defaultErrorResponseClass = 'Drest\\Error\\Response\\Xml';

    /**
     * DOM document
     * @var \DomDocument $xml
     */
    protected $xml;

    /**
     * Name of the current node being parsed
     * @param string $current_node_name
     */
    protected $current_node_name;

    /**
     * @see Drest\Writer\Writer::write()
     */
    public function write(ResultSet $data)
    {
        $this->xml = new \DomDocument('1.0', 'UTF-8');
        $this->xml->formatOutput = true;

        $dataArray = $data->toArray();
        $this->xml->appendChild($this->convertArrayToXml(key($dataArray), $dataArray[key($dataArray)]));
        $this->data = $this->xml->saveXML();
    }

    /**
     * Convert an Array to XML
     * @param string $root_node - name of the root node to be converted
     * @param array $data - array to be converted
     * @throws \Exception
     * @return \DOMNode
     */
    protected function convertArrayToXml($root_node, $data = array())
    {
        if (!$this->isValidTagName($root_node)) {
            throw new \Exception('Array to XML Conversion - Illegal character in element name: ' . $root_node);
        }

        $node = $this->xml->createElement($root_node);

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->current_node_name = $root_node;
                $key = (is_numeric($key)) ? Inflector::singularize($this->current_node_name) : $key;
                $node->appendChild($this->convertArrayToXml($key, $value));
                unset($data[$key]);
            }
        } else {
            $node->appendChild($this->xml->createTextNode($this->bool2str($data)));
        }

        return $node;
    }

    /**
     * Convert an XML to Array
     * @param bool $includeKey
     * @throws \Exception
     * @return array
     */
    public function toArray($includeKey = true)
    {
        $result = array();
        if (!$this->xml instanceof \DomDocument) {
            throw new \Exception('Xml data hasn\'t been loaded. Use either ->write() or ->createFromString() to create it');
        }

        if ($includeKey) {
            $result[$this->xml->documentElement->tagName] = $this->convertXmlToArray($this->xml->documentElement);
        } else {
            $result = $this->convertXmlToArray($this->xml->documentElement);
        }

        return $result;
    }

    /**
     * @see \Drest\Representation\InterfaceRepresentation::createFromString($string)
     */
    public static function createFromString($string)
    {
        $instance = new self();

        $instance->xml = new \DomDocument('1.0', 'UTF-8');
        $instance->xml->formatOutput = true;

        if (!$instance->xml->loadXML($string)) {
            throw new \Exception('Unable to load XML document from string');
        }

        $instance->data = $instance->xml->saveXML();
        return $instance;
    }

    /**
     * recursive function to convert an XML document into an array
     * @param \DOMElement|\DOMText $node
     * @return array $response
     */
    protected function convertXmlToArray($node)
    {
        $output = array();
        switch ($node->nodeType) {
            case XML_ELEMENT_NODE:
                foreach ($node->childNodes as $childNode) {
                    $conversion = $this->convertXmlToArray($childNode);
                    if (isset($childNode->tagName)) {
                        if ($node->tagName === Inflector::pluralize($childNode->tagName)) {
                            $output[] = $conversion;
                        } else {
                            $output[$childNode->tagName] = $conversion;
                        }
                    } elseif (!empty($conversion)) {
                        $output = $conversion;
                    }
                }
                break;
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
        }
        return !empty($output) ? $output : '';
    }

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
     * Get string representation of boolean value
     */
    protected function bool2str($v)
    {
        if (is_bool($v)) {
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
        try {
            new \DOMElement(':' . $tag);
            return true;
        } catch (\DOMException $e) {
            return false;
        }
    }
}