<?php
namespace DrestTests\Functional\Service\Action;

use DrestTests\DrestFunctionalTestCase;

class GetElementTest extends DrestFunctionalTestCase
{

    public function testGetElementRequest()
    {
        $dm = $this->_getDrestManager($this->_em);
        $representation = new \DrestCommon\Representation\Json();

        $user = new \DrestTests\Entities\Typical\User();

        $email = 'frodo.baggin@theshire.com';
        $username = 'frodo.baggins';
        $user->setEmailAddress($email);
        $user->setUsername($username);

        $this->_em->persist($user);
        $this->_em->flush();

        $this->_em->refresh($user);

        $this->assertEquals($email, $user->getEmailAddress());
        $this->assertEquals($username, $user->getUsername());

        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/user/' . $user->getId(),
            'GET',
            array(),
            array(),
            array(),
            array('HTTP_ACCEPT' => $representation->getContentType())
        );

        $response = $dm->dispatch($request);

        $representation = $representation::createFromString($response->getBody());
        $userArray = $representation->toArray(false);

        $this->assertEquals($user->getEmailAddress(), $userArray['email_address']);
        $this->assertEquals($user->getUsername(), $userArray['username']);
    }

}
