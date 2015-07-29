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
