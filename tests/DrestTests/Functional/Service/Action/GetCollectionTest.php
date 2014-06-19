<?php
namespace DrestTests\Functional\Service\Action;

use DrestTests\DrestFunctionalTestCase;

class GetCollectionTests extends DrestFunctionalTestCase
{

    public function testEmptyRequest()
    {
        $dm = $this->_getDrestManager();

        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/users',
            'GET'
        );
        $response = $dm->dispatch($request);

        $this->assertEquals(404, $response->getStatusCode());
    }


    public function testGetCollectionRequest()
    {
        $dm = $this->_getDrestManager($this->_em);
        $representation = new \DrestCommon\Representation\Json();

        $users = array(
            array('email_address' => 'frodo.baggin@theshire.com', 'username' => 'frodo.baggins'),
            array('email_address' => 'samwise.gamgee@theshire.com', 'username' => 'samwise.gamgee')
        );

        foreach ($users as $user) {
            $userObj = new \DrestTests\Entities\CMS\User();
            $userObj->setEmailAddress($user['email_address']);
            $userObj->setUsername($user['username']);

            $this->_em->persist($userObj);
            $this->_em->flush();
        }

        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/users',
            'GET',
            array(),
            array(),
            array(),
            array('HTTP_ACCEPT' => $representation->getContentType())
        );

        $response = $dm->dispatch($request);

        $representation = $representation::createFromString($response->getBody());
        $usersArray = $representation->toArray(false);

        for ($x = 0; $x < sizeof($users); $x++) {
            $this->assertEquals($users[$x]['email_address'], $usersArray[$x]['email_address']);
            $this->assertEquals($users[$x]['username'], $usersArray[$x]['username']);
        }
    }

}