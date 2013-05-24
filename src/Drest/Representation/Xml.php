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
	 * (non-PHPdoc)
	 * @see Drest\Representation\InterfaceRepresentation::createFromString($string)
	 */
	public static function createFromString($string)
	{
        $instance = new self();

        $instance->xml = simplexml_load_string($string);
        if (!$instance->xml)
        {
            throw new \Exception('Unable to load XML document from string');
        }
        $instance->data = $instance->xml->saveXML();
        return $instance;
	}

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
     * @param array $data - aray to be converterd
     * @return DOMNode
     */
    protected function convertArrayToXml($root_node, $data = array())
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
                $node->appendChild($this->convertArrayToXml($key, $value));
                unset($data[$key]);
            }
        } else
        {
            $node->appendChild($this->xml->createTextNode($this->bool2str($data)));
        }

        return $node;
    }

    /**
     *
     * Convert an XML to Array
     * @param \SimpleXMLElement $input_xml
     */
    public function toArray()
    {
	    $this->xml =  new \DomDocument('1.0', 'UTF-8');
	    $this->xml->formatOutput = true;

	    if (!$this->xml instanceof \SimpleXMLElement)
	    {
	         throw new \Exception('Xml data hasn\'t been loaded. Use either ->write() or ->createFromString() to create it');
	    }


		if(is_string($input_xml)) {
			$parsed = $xml->loadXML($input_xml);
			if(!$parsed) {
				throw new Exception('[XML2Array] Error parsing the XML string.');
			}
		} else {
			if(get_class($input_xml) != 'DOMDocument') {
				throw new Exception('[XML2Array] The input XML object should be of type: DOMDocument.');
			}
			$xml = self::$xml = $input_xml;
		}

		$array[$xml->documentElement->tagName] = self::convert($xml->documentElement);
        self::$xml = null;    // clear the xml node in the class for 2nd time use.
        return $array;
    }

    protected function convertXmlToArray($node)
    {
		$output = array();

		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
				$output['@cdata'] = trim($node->textContent);
				break;

			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;

			case XML_ELEMENT_NODE:

				// for each child node, call the covert function recursively
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
					$child = $node->childNodes->item($i);
					$v = self::convert($child);
					if(isset($child->tagName)) {
						$t = $child->tagName;

						// assume more nodes of same kind are coming
						if(!isset($output[$t])) {
							$output[$t] = array();
						}
						$output[$t][] = $v;
					} else {
						//check if it is not an empty text node
						if($v !== '') {
							$output = $v;
						}
					}
				}

				if(is_array($output)) {
					// if only one node of its kind, assign it directly instead if array($value);
					foreach ($output as $t => $v) {
						if(is_array($v) && count($v)==1) {
							$output[$t] = $v[0];
						}
					}
					if(empty($output)) {
						//for empty nodes
						$output = '';
					}
				}

				// loop through the attributes and collect them
				if($node->attributes->length) {
					$a = array();
					foreach($node->attributes as $attrName => $attrNode) {
						$a[$attrName] = (string) $attrNode->value;
					}
					// if its an leaf node, store the value in @value instead of directly storing it.
					if(!is_array($output)) {
						$output = array('@value' => $output);
					}
					$output['@attributes'] = $a;
				}
				break;
		}
		return $output;
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