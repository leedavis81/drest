<?php
namespace DrestTests\Functional;

use DrestTests\DrestFunctionalTestCase;

class ManagerTest extends DrestFunctionalTestCase
{

    public function testExecutingOnANamedRoute()
    {
        $dm = $this->_getDrestManager();


        $response = $dm->dispatch(null, null, 'DrestTests\Entities\Typical\User::get_user');


    }
}