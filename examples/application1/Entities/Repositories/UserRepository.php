<?php
namespace Entities\Repositories;


use Drest\Repository;

/**
 * UserRepository
 */
class UserRepository extends Repository
{

    public function getUser()
    {
        return array('title' => 'Mr', 'firstname' => 'Drest', 'surname' => 'user');
    }

}