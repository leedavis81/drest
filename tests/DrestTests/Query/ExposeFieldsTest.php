<?php
namespace DrestTests\Query;

use DrestTests\DrestTestCase,
    Drest\Mapping\RouteMetaData,
    Drest\Query\ExposeFields,
    AltrEgo\AltrEgo;

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
     * @expectedException Drest\Query\InvalidExposeFieldsException
     */
	public function testParseExposeStringUnclosedBracket()
	{
	    $aeExpose = AltrEgo::create(ExposeFields::create(new RouteMetaData()));
	    $exposeString = 'username|profile[id|lastname';

	    $aeExpose->parseExposeString($exposeString);
	}

    /**
     * @expectedException Drest\Query\InvalidExposeFieldsException
     */
	public function testParseExposeStringInvalidString()
	{
	    $aeExpose = AltrEgo::create(ExposeFields::create(new RouteMetaData()));
	    $exposeString = 'username|profile&';

	    $aeExpose->parseExposeString($exposeString);
	}

	/**
	 *
	 * @todo: shouldn't need to pass by reference to make this work!
	 */
	public function testfilterRequestedExpose()
	{
        $aeExpose = AltrEgo::create(ExposeFields::create(new RouteMetaData()));

        $requested = array('username', 'address');
        $allowed = array('username', 'email');
        $aeExpose->filterRequestedExpose(&$requested, &$allowed);

        $expected = array('username');
	    $this->assertEquals($expected, $requested);
	}






}