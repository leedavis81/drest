<?php
namespace DrestTests\Representation;

use Drest\Query\ResultSet;
use Drest\Representation\Json;
use DrestTests\DrestTestCase;

class JsonTest extends DrestTestCase
{

    protected function getJsonString()
    {
        return '{"user":{"username":"leedavis81","email_address":"lee.davis@somewhere.com","profile":{"id":"1","title":"mr","firstname":"lee","lastname":"davis"},"phone_numbers":[{"id":"1","number":"2087856458"},{"id":"2","number":"2087865978"},{"id":"3","number":"2074855978"}]}}';
    }

    protected function getJsonArray()
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

    public function testArrayToJsonMatches()
    {
        $representation = new Json();
        $array = $this->getJsonArray();
        $resultSet = ResultSet::create($array['user'], 'user');

        $this->assertInstanceOf('Drest\Representation\Json', $representation);

        $this->assertEquals($this->getJsonString(), $representation->output($resultSet));
    }

    public function testJsonToArrayMatches()
    {
        $representation = Json::createFromString($this->getJsonString());

        $this->assertInstanceOf('Drest\Representation\Json', $representation);

        $this->assertEquals($this->getJsonArray(), $representation->toArray());
    }
}