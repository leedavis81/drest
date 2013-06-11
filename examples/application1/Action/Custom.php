<?php
namespace Action;


use Drest\Service\Action\AbstractAction,
    Drest\Query\ResultSet;

class Custom extends AbstractAction
{

    /** (non-PHPdoc)
     * @see Drest\Service\Action.AbstractAction::execute()
     */
    public function execute()
    {
        // execute my own custom logic
         return ResultSet::create(array('name' => 'lee', 'email' => 'lee@somedomain.com'), 'user');
    }

}