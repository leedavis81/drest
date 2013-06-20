<?php
namespace Drest\Tools\Console\Helper;

use Drest\Manager;
use Symfony\Component\Console\Helper\Helper;

/**
 * Drest Manager Helper
 */
class DrestManagerHelper extends Helper
{
    /**
     * Drest Manager
     * @var Manager $dm
     */
    protected $dm;

    /**
     * @param Manager $dm
     */
    public function __construct(Manager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Get Drest Manager
     * @return Manager
     */
    public function getDrestManager()
    {
        return $this->dm;
    }

    /**
     * @see \Symfony\Component\Console\Helper\HelperInterface::getName()
     */
    public function getName()
    {
        return 'drestManager';
    }
}