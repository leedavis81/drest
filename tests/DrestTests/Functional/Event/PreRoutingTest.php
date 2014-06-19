<?php
namespace DrestTests\Functional\Event;

use Drest\Event\Manager;
use DrestTests\DrestFunctionalTestCase;

class PreRoutingTest extends DrestFunctionalTestCase
{

    public function testOnPreRoutingListener()
    {
        // Sets the body to the 'return' parameter contents
        $listener = new OnPreRoutingListener();

        $evm = new Manager();
        $evm->addEventListener(\Drest\Event\Events::PRE_ROUTING, $listener);

        $dm = $this->_getDrestManager($this->_em, null, $evm);

        $xHeader = '12345zyxabc';
        $request = \Symfony\Component\HttpFoundation\Request::create('/users?xHeader=' . $xHeader, 'GET');

        $response = $dm->dispatch($request);

        $this->assertEquals($xHeader, $response->getHttpHeader('xHeader'));
    }

    public function testOnPreRoutingSubscriber()
    {
        $subscriber = new OnPreRoutingSubscriber();

        $evm = new Manager();
        $evm->addEventSubscriber($subscriber);

        $dm = $this->_getDrestManager($this->_em, null, $evm);

        $xHeader = '12345zyxabc';
        $request = \Symfony\Component\HttpFoundation\Request::create('/users?xHeader=' . $xHeader, 'GET');

        $response = $dm->dispatch($request);

        $this->assertEquals($xHeader, $response->getHttpHeader('xHeader'));
    }

}

class OnPreRoutingListener
{
    public function PreRouting(\Drest\Event\PreRoutingArgs $args)
    {
        $service = $args->getService();
        // set a header from sent parameters
        $service->getResponse()->setHttpHeader('xHeader', $service->getRequest()->getParams('xHeader'));
    }
}

class OnPreRoutingSubscriber extends \Drest\Event\Subscriber
{
    public function PreRouting(\Drest\Event\PreRoutingArgs $args)
    {
        $service = $args->getService();
        // set a header from sent parameters
        $service->getResponse()->setHttpHeader('xHeader', $service->getRequest()->getParams('xHeader'));
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(\Drest\Event\Events::PRE_ROUTING);
    }
}
