<?php
namespace Service;

use Drest\Query\ResultSet;

use Drest\Service\AbstractService;

class User extends AbstractService
{


    public function getMyCustomElement()
    {
        $resultSet = ResultSet::create(array('title' => 'mr', 'name' => 'lee', 'email' => 'sdfsdf@sdfsdf.com'), 'user');

        $this->renderDeterminedRepresentation($resultSet);
    }

    public function postMyElement()
    {

    }
}