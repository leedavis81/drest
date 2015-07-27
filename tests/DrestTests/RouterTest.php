<?php
namespace DrestTests;

use Doctrine\Common\Cache\ArrayCache;
use Drest\Configuration;

class RouterTest extends DrestTestCase
{

    public function testAddingRouteToRouter()
    {
        $router = new \Drest\Router();
        $routeMetaData = new \Drest\Mapping\RouteMetaData();
        $routeName = 'MyRoute';
        $routeMetaData->setName($routeName);
        $router->registerRoute($routeMetaData);

        $this->assertTrue($router->hasRoute($routeName));
    }

    public function testMatchingSingleRoutePattern()
    {
        $router = new \Drest\Router();

        $routeMetaData = new \Drest\Mapping\RouteMetaData();
        $routeMetaData->setRoutePattern('/user/:id');
        $router->registerRoute($routeMetaData);

        $request = new \DrestCommon\Request\Request(\Symfony\Component\HttpFoundation\Request::create('/user/1'));

        $matchedRoutes = $router->getMatchedRoutes($request);
        $this->assertCount(1, $matchedRoutes);

        $routeMetaData = new \Drest\Mapping\RouteMetaData();
        $routeMetaData->setRoutePattern('/user');
        $router->registerRoute($routeMetaData);

        $request = new \DrestCommon\Request\Request(\Symfony\Component\HttpFoundation\Request::create('/user/1'));
        $matchedRoutes = $router->getMatchedRoutes($request);
        $this->assertCount(0, $matchedRoutes);
    }

    public function testRouteWithABasePath()
    {
        $router = new \Drest\Router();
        $router->setRouteBasePaths(array('v1', 'v2'));

        $routeMetaData = new \Drest\Mapping\RouteMetaData();
        $routeMetaData->setRoutePattern('/user/:id');
        $router->registerRoute($routeMetaData);

        $request = new \DrestCommon\Request\Request(\Symfony\Component\HttpFoundation\Request::create('/user/1'));
        $this->assertCount(0, $router->getMatchedRoutes($request));

        $request = new \DrestCommon\Request\Request(\Symfony\Component\HttpFoundation\Request::create('/v1/user/1'));
        $this->assertCount(1, $router->getMatchedRoutes($request));

        $request = new \DrestCommon\Request\Request(\Symfony\Component\HttpFoundation\Request::create('/v2/user/1'));
        $this->assertCount(1, $router->getMatchedRoutes($request));
    }
}