<?php
namespace DrestTests\Service;

use Drest\Service\Action\Registry;
use DrestTests\DrestTestCase;

class RegistryTest extends DrestTestCase
{

    public function testAddingToRegistry()
    {
        $registry = new Registry();
        $this->assertInstanceOf('Drest\Service\Action\Registry', $registry);

        $className = 'DrestTests\\Entities\\Typical\\User';
        $routeName = 'route_name';

        $serviceAction = new \DrestTests\Action\Custom();
        $registry->register($serviceAction, [$className . '::' . $routeName]);

        $routeMetaData = new \Drest\Mapping\RouteMetaData();
        $routeMetaData->setClassMetaData(new \Drest\Mapping\ClassMetaData(new \ReflectionClass($className)));
        $routeMetaData->setName($routeName);

        $this->assertTrue($registry->hasServiceAction($routeMetaData));
    }

    public function testNotAddingToRegistry()
    {
        $registry = new Registry();

        $className = 'DrestTests\\Entities\\Typical\\User';
        $routeName = 'route_name';

        $routeMetaData = new \Drest\Mapping\RouteMetaData();
        $routeMetaData->setClassMetaData(new \Drest\Mapping\ClassMetaData(new \ReflectionClass($className)));
        $routeMetaData->setName($routeName);

        $this->assertFalse($registry->hasServiceAction($routeMetaData));
    }

    public function testRemovingFromRegistryByAction()
    {
        $registry = new Registry();

        $className = 'DrestTests\\Entities\\Typical\\User';
        $routeName = 'route_name';

        $serviceAction = new \DrestTests\Action\Custom();
        $registry->register($serviceAction, [$className . '::' . $routeName]);

        $routeMetaData = new \Drest\Mapping\RouteMetaData();
        $routeMetaData->setClassMetaData(new \Drest\Mapping\ClassMetaData(new \ReflectionClass($className)));
        $routeMetaData->setName($routeName);

        $registry->unregisterByAction($serviceAction);

        $this->assertFalse($registry->hasServiceAction($routeMetaData));
    }

    public function testRemovingFromRegistryByRoute()
    {
        $registry = new Registry();

        $className = 'DrestTests\\Entities\\Typical\\User';
        $routeName = 'route_name';

        $serviceAction = new \DrestTests\Action\Custom();
        $registry->register($serviceAction, [$className . '::' . $routeName]);

        $routeMetaData = new \Drest\Mapping\RouteMetaData();
        $routeMetaData->setClassMetaData(new \Drest\Mapping\ClassMetaData(new \ReflectionClass($className)));
        $routeMetaData->setName($routeName);

        $registry->unregisterByRoute($routeMetaData);

        $this->assertFalse($registry->hasServiceAction($routeMetaData));
    }


    public function testRemovingFromRegistryByRouteAndHavingActionUsedForOtherRoute()
    {
        $registry = new Registry();

        $className = 'DrestTests\\Entities\\Typical\\User';
        $className2 = 'DrestTests\\Entities\\Typical\\User';
        $routeName = 'route_name';
        $routeName2 = 'route_name2';

        $serviceAction = new \DrestTests\Action\Custom();
        $registry->register($serviceAction, [
            $className . '::' . $routeName,
            $className2 . '::' . $routeName2
        ]);

        $routeMetaData = new \Drest\Mapping\RouteMetaData();
        $routeMetaData->setClassMetaData(new \Drest\Mapping\ClassMetaData(new \ReflectionClass($className)));
        $routeMetaData->setName($routeName);

        $routeMetaData2 = new \Drest\Mapping\RouteMetaData();
        $routeMetaData2->setClassMetaData(new \Drest\Mapping\ClassMetaData(new \ReflectionClass($className2)));
        $routeMetaData2->setName($routeName2);

        $registry->unregisterByRoute($routeMetaData);

        $this->assertFalse($registry->hasServiceAction($routeMetaData));
        $this->assertTrue($registry->hasServiceAction($routeMetaData2));

        $this->assertSame($registry->getServiceAction($routeMetaData2), $serviceAction);

        $registry->unregisterByRoute($routeMetaData2);

        $this->assertFalse($registry->hasServiceAction($routeMetaData));
        $this->assertFalse($registry->hasServiceAction($routeMetaData2));
    }
}