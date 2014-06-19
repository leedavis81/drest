<?php
namespace Drest\Event;

class Events
{
    /**
     * Event is triggered before a routing lookup occurs.
     * This event is fired regardless of whether a named route is used.
     *
     * @var string
     */
    const PRE_ROUTING = 'preRouting';

    /**
     * Event is triggered after a routing lookup is performed.
     * This event is fired regardless of whether a named route is used.
     *
     * @var string
     */
    const POST_ROUTING = 'postRouting';

    /**
     * Event is triggered before the service action call is executed
     * If the request has failed a route lookup this event will NOT be executed
     *
     * @var string
     */
    const PRE_SERVICE_ACTION = 'preServiceAction';

    /**
     * Event is triggered after the service action call is executed
     * If the request has failed a route lookup this event will NOT be executed
     *
     * @var string
     */
    const POST_SERVICE_ACTION = 'postServiceAction';

    /**
     * Event is triggered before dispatching the request through the Drest manager
     *
     * @var string
     */
    const PRE_DISPATCH = 'preDispatch';

    /**
     * Event is triggered after dispatching the request through the Drest manager
     * In the event of an error this event will still be executed
     *
     * @var string
     */
    const POST_DISPATCH = 'postDispatch';
}
