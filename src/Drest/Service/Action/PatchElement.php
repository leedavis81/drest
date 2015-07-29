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
namespace Drest\Service\Action;

use Doctrine\ORM;
use DrestCommon\Response\Response;
use DrestCommon\ResultSet;

class PatchElement extends AbstractAction
{

    public function execute()
    {
        return $this->performDefaultUpdateAction();
    }
}
