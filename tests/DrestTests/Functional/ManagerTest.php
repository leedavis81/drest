<?php
namespace DrestTests;



class ManagerTest extends DrestTestCase
{

    public function testExecutingOnANamedRoute()
    {
        $dm = $this->_getDrestManager();

        $dm->dispatch()

    }
}