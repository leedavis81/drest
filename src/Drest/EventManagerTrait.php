<?php
/**
 * This file is part of the Drest package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Lee Davis
 * @copyright Copyright (c) Lee Davis <@leedavis81>
 * @link https://github.com/leedavis81/drest/blob/master/LICENSE
 * @license http://opensource.org/licenses/MIT The MIT X License (MIT)
 */
namespace Drest;

trait EventManagerTrait
{

    /**
     * Event manager object
     * @var Event\Manager
     */
    protected $eventManager;

    /**
     * Get the event manager
     * @return Event\Manager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Trigger the pre dispatch event
     * @param Service $service
     */
    public function triggerPreDispatchEvent(Service $service)
    {
        // trigger preDispatch event
        $this->getEventManager()->dispatchEvent(
            Event\Events::PRE_DISPATCH,
            new Event\PreDispatchArgs($service)
        );
    }

    /**
     * Trigger the post dispatch event
     * @param Service $service
     */
    public function triggerPostDispatchEvent(Service $service)
    {
        // trigger a postDispatch event
        $this->getEventManager()->dispatchEvent(
            Event\Events::POST_DISPATCH,
            new Event\PostDispatchArgs($service)
        );
    }

    /**
     * Trigger the pre routing event
     * @param Service $service
     */
    public function triggerPreRoutingEvent(Service $service)
    {
        $this->getEventManager()->dispatchEvent(
            Event\Events::PRE_ROUTING,
            new Event\PreRoutingArgs($service)
        );
    }

    /**
     * Trigger the post routing event
     * @param Service $service
     */
    public function triggerPostRoutingEvent(Service $service)
    {
        $this->getEventManager()->dispatchEvent(
            Event\Events::POST_ROUTING,
            new Event\PostRoutingArgs($service)
        );
    }


    /**
     * Trigger the pre service action event
     * @param Service $service
     */
    public function triggerPreServiceActionEvent(Service $service)
    {
        $this->getEventManager()->dispatchEvent(
            Event\Events::PRE_SERVICE_ACTION,
            new Event\PreServiceActionArgs($service)
        );
    }

    /**
     * Trigger the post service action event
     * @param Service $service
     */
    public function triggerPostServiceActionEvent(Service $service)
    {
        $this->getEventManager()->dispatchEvent(
            Event\Events::POST_SERVICE_ACTION,
            new Event\PostServiceActionArgs($service)
        );
    }

}