<?php
namespace MyEvents;

use Drest\Event;

class MyEvent
{

    public function preServiceAction(Event\PreServiceActionArgs $args)
    {
        echo 'Pre service action fired';
    }

    public function postServiceAction(Event\PostServiceActionArgs $args)
    {
        echo 'Post service action fired';
    }

    public function preRouting(Event\PreRoutingArgs $arg)
    {
        echo 'Pre routing action fired';
    }

    public function postRouting(Event\PostRoutingArgs $arg)
    {
        echo 'Post routing action fired';
    }

    public function preDispatch(Event\PreDispatchArgs $arg)
    {
        echo 'Pre dispatch action fired';
    }

    public function postDispatch(Event\PostDispatchArgs $arg)
    {
        echo 'Post dispatch action fired';
    }
}