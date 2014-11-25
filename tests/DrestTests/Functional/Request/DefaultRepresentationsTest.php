<?php
namespace DrestTests\Functional\Event;

use Drest\Configuration;
use DrestTests\DrestFunctionalTestCase;

class DefaultRepresentationsTest extends DrestFunctionalTestCase
{
    public function testFallbackToDefaultRepresentations()
    {
        $config = new Configuration();
        $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
        $config->addPathsToConfigFiles(array(__DIR__ . '/../../Entities'));
        $config->setDefaultRepresentations(array('Json'));
        $config->setDebugMode(true);

        $dm = $this->_getDrestManager(null, $config);

        $request = \Symfony\Component\HttpFoundation\Request::create('/no_rep/1', 'GET');
        $response = $dm->dispatch($request);

        // This should 404 (as it doesnt exist)
        $this->assertJsonStringEqualsJsonString('{"error":"An unknown error occured"}', $response->getBody());
    }
}
