<?php
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
