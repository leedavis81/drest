<?php
namespace Drest\Event;

use Doctrine\Common\EventArgs;
use Drest\Service;

class PostRoutingArgs extends EventArgs
{
    /**
     * @var Service $service
     */
    private $service;

    /**
     * @param Service $service
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Get the created service object
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }
}