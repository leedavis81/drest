<?php
namespace DrestTests\Query;

use AltrEgo\AltrEgo;
use Drest\Configuration;
use Drest\Mapping\RouteMetaData;
use Drest\Query\ExposeFields;
use Drest\Request;
use DrestTests\DrestTestCase;

class ExposeFieldsTest extends DrestTestCase
{

    public function testCreateExposeFieldsObject()
    {
        $route = new RouteMetaData();
        $expose = ExposeFields::create($route);

        $this->assertInstanceOf('Drest\Query\ExposeFields', $expose);
    }

    public function testParseExposeString()
    {
        $aeExpose = AltrEgo::create(ExposeFields::create(new RouteMetaData()));

        $exposeString = 'username|email_address|profile[id|lastname|addresses[id]]|phone_numbers';
        $exposeArray = array(
            'username',
            'email_address',
            'profile' => array(
                'id',
                'lastname',
                'addresses' => array(
                    'id'
                )
            ),
            'phone_numbers'
        );

        $this->assertEquals($exposeArray, $aeExpose->parseExposeString($exposeString));
    }

    public function testParseExposeStringEndingWithMultipleCloseBrackets()
    {
        $aeExpose = AltrEgo::create(ExposeFields::create(new RouteMetaData()));

        $exposeString = 'username|email_address|profile[id|lastname|addresses[id]]';
        $exposeArray = array(
            'username',
            'email_address',
            'profile' => array(
                'id',
                'lastname',
                'addresses' => array(
                    'id'
                )
            )
        );

        $this->assertEquals($exposeArray, $aeExpose->parseExposeString($exposeString));
    }

    public function testParseExposeStringEndingWithEmptyOffsets()
    {
        $aeExpose = AltrEgo::create(ExposeFields::create(new RouteMetaData()));

        $exposeString = '||||username||email_address||profile||';
        $exposeArray = array(
            'username',
            'email_address',
            'profile'
        );

        $this->assertEquals($exposeArray, $aeExpose->parseExposeString($exposeString));
    }

    /**
     * @expectedException \Drest\Query\InvalidExposeFieldsException
     */
    public function testParseExposeStringUnclosedBracket()
    {
        $aeExpose = AltrEgo::create(ExposeFields::create(new RouteMetaData()));
        $exposeString = 'username|profile[id|lastname';

        $aeExpose->parseExposeString($exposeString);
    }

    /**
     * @expectedException \Drest\Query\InvalidExposeFieldsException
     */
    public function testParseExposeStringInvalidString()
    {
        $aeExpose = AltrEgo::create(ExposeFields::create(new RouteMetaData()));
        $exposeString = 'username|profile&';

        $aeExpose->parseExposeString($exposeString);
    }

    public function testConfigureExposurePullRequest()
    {
        $routeMetaData = new RouteMetaData();
        $expose = ExposeFields::create($routeMetaData);

        $request = new Request();
        $request->setPost('expose', 'username|address');

        $expose->configurePullRequest(array(
            Configuration::EXPOSE_REQUEST_PARAM_POST => 'expose'
        ), $request);

        // No explicit expose has been set by depth setting, this should be empty
        $this->assertEquals(array(), $expose->toArray());
    }

    public function testConfigureExposurePullRequestWithExplicitExpose()
    {
        $routeMetaData = new RouteMetaData();
        $explicit_expose = array('username', 'email');
        $routeMetaData->setExpose($explicit_expose);

        $expose = ExposeFields::create($routeMetaData);

        $request = new Request();
        $request->setPost('expose', 'username|address');

        $expose->configurePullRequest(array(
            Configuration::EXPOSE_REQUEST_PARAM_POST => 'expose'
        ), $request);

        $this->assertEquals($explicit_expose, $expose->toArray());
    }
}