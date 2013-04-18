<?php

namespace Drest\Writer;

use Drest\DrestException;

/**
 *
 * Conversion borrowed from http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * @author Lee
 */
class Xml extends AbstractWriter
{

    /**
     * DOM document
     * @var DomDocument $xml
     */
    protected $xml;


	public function getMatchableAcceptHeaders()
	{

	}

	public function getMatchableExtensions()
	{

	}

	public function getMatchableFormatParam()
	{

	}


	/**
	 * @see Drest\Writer\Writer::write()
	 */
	public function write($data)
	{
	    $this->xml =  new \DomDocument('1.0', 'UTF-8');
	    $this->xml->formatOutput = true;

	    $this->xml->appendChild($this->convert('response', $data));
	    return $this->xml->saveXML();
	}

    /**
     * Convert an Array to XML
     * @param string $root_node - name of the root node to be converted
     * @param array $data - aray to be converterd
     * @return DOMNode
     */
    protected function convert($root_node, $data = array())
    {
        $node = $this->xml->createElement($root_node);

        if(is_array($data))
        {
            if(isset($data['@attributes']))
            {
                foreach($data['@attributes'] as $key => $value)
                {
                    if(!$this->isValidTagName($key))
                    {
                        throw new \Exception('[Array2XML] Illegal character in attribute name. attribute: '.$key.' in node: '. $root_node);
                    }
                    $node->setAttribute($key, $this->bool2str($value));
                }
                unset($data['@attributes']);
            }

            if(isset($data['@value']))
            {
                $node->appendChild($this->xml->createTextNode($this->bool2str($data['@value'])));
                unset($data['@value']);
                return $node;
            } else if(isset($data['@cdata']))
            {
                $node->appendChild($this->xml->createCDATASection($this->bool2str($data['@cdata'])));
                unset($data['@cdata']);
                return $node;
            }
        }

        if(is_array($data))
        {
            foreach($data as $key => $value)
            {
                if(!$this->isValidTagName($key))
                {
                    throw new \Exception('[Array2XML] Illegal character in tag name. tag: '.$key.' in node: '. $root_node);
                }
                if(is_array($value) && is_numeric(key($value)))
                {
                    foreach($value as $k=>$v)
                    {
                        $node->appendChild($this->convert($key, $v));
                    }
                } else {
                    $node->appendChild($this->convert($key, $value));
                }
                unset($data[$key]);
            }
        } else
        {
            $node->appendChild($this->xml->createTextNode($this->bool2str($data)));
        }

        return $node;
    }

    /*
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

    /*
     * Check if the tag name or attribute name contains illegal characters
     * Ref: http://www.w3.org/TR/xml/#sec-common-syn
     */
    protected function isValidTagName($tag)
    {
        $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
        return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
    }
}