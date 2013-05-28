<?php
namespace DrestTests\Representation;


use DrestTests\DrestTestCase,
    Drest\Query\ResultSet,
    Drest\Representation\Xml;

class XmlTest extends DrestTestCase
{

    protected function getXmlString($formatted = true)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<user>
  <username>leedavis81</username>
  <email_address>lee.davis@somewhere.com</email_address>
  <profile>
    <id>1</id>
    <title>mr</title>
    <firstname>lee</firstname>
    <lastname>davis</lastname>
  </profile>
  <phone_numbers>
    <phone_number>
      <id>1</id>
      <number>2087856458</number>
    </phone_number>
    <phone_number>
      <id>2</id>
      <number>2087865978</number>
    </phone_number>
    <phone_number>
      <id>3</id>
      <number>2074855978</number>
    </phone_number>
  </phone_numbers>
</user>';
        return ($formatted) ? $xml : $this->removeXmlFormatting($xml);
    }

    private function removeXmlFormatting($string)
    {
        return str_replace(array(" ", "\n", "\r"), '', $string);
    }

    protected function getXmlArray()
    {
        return array('user' => array(
        	'username' => 'leedavis81',
            'email_address' => 'lee.davis@somewhere.com',
            'profile' => array(
                'id' => '1',
                'title' => 'mr',
                'firstname' => 'lee',
                'lastname' => 'davis',
            ),
            'phone_numbers' => array(
                array(
                	'id' => '1',
                    'number' => '2087856458'
                ),
                array(
                	'id' => '2',
                    'number' => '2087865978'
                ),
                array(
                	'id' => '3',
                    'number' => '2074855978'
                )
            )
        ));
    }

	public function testArrayToXmlMatches()
	{
        $representation = new Xml();
        $array = $this->getXmlArray();
        $resultSet = ResultSet::create($array['user'], 'user');

        $this->assertInstanceOf('Drest\Representation\Xml', $representation);
        $this->assertEquals($this->getXmlString(false), $this->removeXmlFormatting($representation->output($resultSet)));
	}

	public function testXmlToArrayMatches()
	{
	    $representation = Xml::createFromString($this->getXmlString());

	    $this->assertInstanceOf('Drest\Representation\Xml', $representation);

	    $this->assertEquals($this->getXmlArray(), $representation->toArray());
	}

}