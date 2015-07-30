<?php
namespace DrestTests\Functional;

use Drest\Configuration;
use DrestTests\DrestFunctionalTestCase;

class ManagerTest extends DrestFunctionalTestCase
{

    public function testExecutingOnANamedRoute()
    {
        $dm = $this->_getDrestManager();
        $dm->dispatch(null, null, 'DrestTests\Entities\Typical\User::get_user');
    }

    /**
     * @expectedException \Drest\DrestException
     */
    public function testExecutingOnAMissingNamedRoute()
    {
        $dm = $this->_getDrestManager();
        $dm->dispatch(null, null, 'DrestTests\Entities\Typical\User::this12doesnt34exist56');
    }

    /** @expectedException \Drest\Route\NoMatchException */
    public function testNoRouteMatchException()
    {
        $config = new Configuration();
        $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
        $config->addPathsToConfigFiles(array(__DIR__ . '/../Entities/Typical'));
        $config->setDefaultRepresentations(array('Json'));
        $config->setDebugMode(true);

        $dm = $this->_getDrestManager(null, $config);

        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/this-doesnt-exist',
            'GET'
        );

        $dm->dispatch($request);
    }

    
    public function testNoRouteMatchExceptionReturnedInAcceptedFormat()
    {
        $config = new Configuration();
        $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
        $config->addPathsToConfigFiles(array(__DIR__ . '/../Entities/Typical'));
        $config->setDefaultRepresentations(array('Json'));
        $config->setDebugMode(false);  // Triggers an actual return document

        $dm = $this->_getDrestManager(null, $config);

        $representation = new \DrestCommon\Representation\Json();
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/this-doesnt-exist',
            'GET',
            array(),
            array(),
            array(),
            array('HTTP_ACCEPT' => $representation->getContentType())
        );

        /** @var \DrestCommon\Response\Response $response */
        $response = $dm->dispatch($request);

        $this->assertInstanceOf('DrestCommon\Response\Response', $response);
        // Ensure we can decode as a json string
        $responseDocument = json_decode($response->getBody(), true);
        $this->assertTrue(isset($responseDocument['error']));
    }
}