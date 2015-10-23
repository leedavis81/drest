<?php

namespace DrestTests\Mapping;

use DrestTests\DrestTestCase;

class RouteTest extends DrestTestCase
{

    public function testRouteAnnotationArrayAccess()
    {
        $route = new \Drest\Mapping\Annotation\Route();

        $route->action = 'action';
        $this->assertEquals('action', $route['action']);

        unset($route['action']);

        $this->assertTrue(!isset($route->action));
    }
}