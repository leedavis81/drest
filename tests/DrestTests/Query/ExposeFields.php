<?php
namespace DrestTests\Query;


use DrestTests\DrestTestCase,
    Drest\Mapping\RouteMetaData,
    Drest\Query\ExposeFields;

class ExposeFieldsTest extends DrestTestCase
{


	public function testCreateExposeFieldsObject()
	{
	    $route = new RouteMetaData();
		$expose = ExposeFields::create($route);

		$this->assertInstanceOf('Drest\Query\ExposeFields', $expose);
	}


	public function testConfigureHardExposureValue()
	{
        $expose = array('username', 'email_address');
	    $route = new RouteMetaData();
	    $route->setExpose($expose);

//
//		$expose = ExposeFields::create($route);
//		$expose->configureExposeDepth($em, 1);
//
//		$expose->configureExposureRequest($requestOptions, $request)
//
//		$expose->toArray()
	}

}