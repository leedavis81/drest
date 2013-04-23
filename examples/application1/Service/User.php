<?php
namespace Service;

use Drest\Service\AbstractService;

class User extends AbstractService
{


    public function getMyCustomElement()
    {
        return array('title' => 'mr', 'name' => 'lee', 'email' => 'sdfsdf@sdfsdf.com');
    }
}