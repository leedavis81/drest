<?php
namespace Action;


use DrestCommon\ResultSet;
use Drest\Service\Action\AbstractAction;

class Custom extends AbstractAction
{

    /**
     * @see Drest\Service\Action\AbstractAction::execute()
     */
    public function execute()
    {
        // execute my own custom logic
        return ResultSet::create(array('name' => 'lee', 'email' => 'lee@somedomain.com'), 'user');
    }
}