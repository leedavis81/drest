<?php
namespace Drest\Event;

use Drest\Service;
use Doctrine\Common\EventArgs;

class PostServiceActionArgs extends EventArgs
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