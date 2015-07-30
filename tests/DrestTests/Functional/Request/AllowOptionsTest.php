<?php
namespace DrestTests\Functional\Event;

use Drest\Configuration;
use DrestTests\DrestFunctionalTestCase;

class AllowOptionsTest extends DrestFunctionalTestCase
{

    public function testAllowOptionsResponse()
    {
        $config = new Configuration();
        $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
        $config->addPathsToConfigFiles(array(__DIR__ . '/../../Entities/Typical'));
        $config->setDebugMode(true);

        $config->setAllowOptionsRequest(true);

        $dm = $this->_getDrestManager(null, $config);

        $request = \Symfony\Component\HttpFoundation\Request::create('/user/1', 'OPTIONS');

        $response = $dm->dispatch($request);

        $allow = explode(',', $response->getHttpHeader('Allow'));
        $allow = array_map(
            function ($item) {
                return trim($item);
            },
            $allow
        );
        // sort it so we can array_diff
        sort($allow);

        // Allow should show PUT, PATCH, DELETE, GET as in Typical/User.php
        $this->assertCount(
            0,
            array_diff(
                array('DELETE', 'GET', 'PATCH', 'PUT'),
                $allow
            )
        );

    }

    /**
     * @expectedException \Drest\Route\NoMatchException
     */
    public function testAllowOptionsSetToFalse()
    {
        $config = new Configuration();
        $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
        $config->addPathsToConfigFiles(array(__DIR__ . '/../../Entities/Typical'));
        $config->setDebugMode(true);

        $config->setAllowOptionsRequest(false);

        $dm = $this->_getDrestManager(null, $config);

        $request = \Symfony\Component\HttpFoundation\Request::create('/user/1', 'OPTIONS');

        $dm->dispatch($request);
    }

}
