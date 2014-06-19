<?php
namespace DrestTests\Functional\Event;

use Drest\Event\Manager;
use DrestTests\DrestFunctionalTestCase;

class PreDispatchTest extends DrestFunctionalTestCase
{

    public function testOnPreDispatchListener()
    {
        // Sets the body to the 'return' parameter contents
        $listener = new OnPreDispatchListener();

        $evm = new Manager();
        $evm->addEventListener(\Drest\Event\Events::PRE_DISPATCH, $listener);

        $dm = $this->_getDrestManager($this->_em, null, $evm);

        $xHeader = '12345zyxabc';
        $request = \Symfony\Component\HttpFoundation\Request::create('/users?xHeader=' . $xHeader, 'GET');

        $response = $dm->dispatch($request);

        $this->assertEquals($xHeader, $response->getHttpHeader('xHeader'));
    }

    public function testOnPreDispatchSubscriber()
    {
        $subscriber = new OnPreDispatchSubscriber();

        $evm = new Manager();
        $evm->addEventSubscriber($subscriber);

        $dm = $this->_getDrestManager($this->_em, null, $evm);

        $xHeader = '12345zyxabc';
        $request = \Symfony\Component\HttpFoundation\Request::create('/users?xHeader=' . $xHeader, 'GET');

        $response = $dm->dispatch($request);

        $this->assertEquals($xHeader, $response->getHttpHeader('xHeader'));
    }

}

class OnPreDispatchListener
{
    public function preDispatch(\Drest\Event\PreDispatchArgs $args)
    {
        $service = $args->getService();
        // set a header from sent parameters
        $service->getResponse()->setHttpHeader('xHeader', $service->getRequest()->getParams('xHeader'));
    }
}

class OnPreDispatchSubscriber extends \Drest\Event\Subscriber
{
    public function preDispatch(\Drest\Event\PreDispatchArgs $args)
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
        return array(\Drest\Event\Events::PRE_DISPATCH);
    }
}
