<?php

namespace Drest\Tools\Console\Helper;

use Symfony\Component\Console\Helper\Helper,
    Drest\Manager;


/**
 * Drest Manager Helper
 */
class DrestManagerHelper extends Helper
{

    /**
     * Drest Manager
     * @var DrestManager
     */
    protected $dm;

    /**
     * @param Drest\Manager $dm
     */
    public function __construct(DrestManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Get Drest Manager
     *
     * @return Drest\Manager
     */
    public function getDrestManager()
    {
        return $this->dm;
    }

    /**
     * @see Symfony\Component\Console\Helper.HelperInterface::getName()
     */
    public function getName()
    {
        return 'drestManager';
    }

}