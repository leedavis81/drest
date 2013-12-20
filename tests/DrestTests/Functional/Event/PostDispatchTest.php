<?php
namespace DrestTests\Functional\Event;


use Drest\Event\Manager;
use DrestTests\DrestFunctionalTestCase;

class PostDispatchTest extends DrestFunctionalTestCase
{

    public function testOnPostDispatchListener()
    {
        // Sets the body to the 'return' parameter contents
        $listener = new OnPostDispatchListener();

        $evm = new Manager();
        $evm->addEventListener(\Drest\Event\Events::POST_DISPATCH, $listener);

        $dm = $this->_getDrestManager($this->_em, null, $evm);

        $return = '12345abczyx';
        $request = \Symfony\Component\HttpFoundation\Request::create('/users?return=' . $return, 'GET');

        $response = $dm->dispatch($request);

        $this->assertEquals($return, $response->getBody());
    }

    public function testOnPostDispatchSubscriber()
    {
        // Sets the body to the 'return' parameter contents
        $subscriber = new OnPostDispatchSubscriber();

        $evm = new Manager();
        $evm->addEventSubscriber($subscriber);

        $dm = $this->_getDrestManager($this->_em, null, $evm);

        $return = '12345zyxabc';
        $request = \Symfony\Component\HttpFoundation\Request::create('/users?return=' . $return, 'GET');

        $response = $dm->dispatch($request);

        $this->assertEquals($return, $response->getBody());
    }

}


class OnPostDispatchListener
{
    public function postDispatch(\Drest\Event\PostDispatchArgs $args)
    {
        // This should be the last event in the stack, the response should never be modified after this point.
        $args->getService()->getResponse()->setBody($args->getService()->getRequest()->getParams('return'));
    }
}


class OnPostDispatchSubscriber extends \Drest\Event\Subscriber
{
    public function postDispatch(\Drest\Event\PostDispatchArgs $args)
    {
        // This should be the last event in the stack, the response should never be modified after this point.
        $args->getService()->getResponse()->setBody($args->getService()->getRequest()->getParams('return'));
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(\Drest\Event\Events::POST_DISPATCH);
    }
}