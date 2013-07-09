<?php
namespace DrestTests\Functional\Event;


use Drest\Event\Manager;
use DrestTests\DrestFunctionalTestCase;

class PreServiceActionTest extends DrestFunctionalTestCase
{

    public function testOnPreServiceActionListener()
    {
        // Sets the body to the 'return' parameter contents
        $listener = new OnPreServiceActionListener();

        $evm = new Manager();
        $evm->addEventListener(\Drest\Event\Events::preServiceAction, $listener);

        $dm = $this->_getDrestManager($this->_em, null, $evm);

        $xHeader = '12345zyxabc';
        $request = \Symfony\Component\HttpFoundation\Request::create('/users?xHeader=' . $xHeader, 'GET');

        $response = $dm->dispatch($request);

        $this->assertEquals($xHeader, $response->getHttpHeader('xHeader'));
    }

    public function testOnPreServiceActionSubscriber()
    {
        $subscriber = new OnPreServiceActionSubscriber();

        $evm = new Manager();
        $evm->addEventSubscriber($subscriber);

        $dm = $this->_getDrestManager($this->_em, null, $evm);

        $xHeader = '12345zyxabc';
        $request = \Symfony\Component\HttpFoundation\Request::create('/users?xHeader=' . $xHeader, 'GET');

        $response = $dm->dispatch($request);

        $this->assertEquals($xHeader, $response->getHttpHeader('xHeader'));
    }

}


class OnPreServiceActionListener
{
    public function PreServiceAction(\Drest\Event\PreServiceActionArgs $args)
    {
        $service = $args->getService();
        // set a header from sent parameters
        $service->getResponse()->setHttpHeader('xHeader', $service->getRequest()->getParams('xHeader'));
    }
}


class OnPreServiceActionSubscriber extends \Drest\Event\Subscriber
{
    public function PreServiceAction(\Drest\Event\PreServiceActionArgs $args)
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
        return array(\Drest\Event\Events::preServiceAction);
    }
}