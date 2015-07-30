<?php
namespace DrestTests\Functional\Service\Action;

use DrestTests\DrestFunctionalTestCase;

class PutElementTest extends DrestFunctionalTestCase
{

    public function testPutElementRequest()
    {
        $dm = $this->_getDrestManager($this->_em);
        $representation = new \DrestCommon\Representation\Json();

        $user = new \DrestTests\Entities\Typical\User();
        $user->setEmailAddress('hello@somewhere.com');
        $user->setUsername('leedavis81');

        $this->_em->persist($user);
        $this->_em->flush();
        $this->_em->refresh($user);

        $putEmail = 'goodbye@nowhere.com';

        $representation->write(\DrestCommon\ResultSet::create(array('email_address' => $putEmail), 'user'));

        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/user/' . $user->getId(),
            'PUT',
            array(),
            array(),
            array(),
            array('HTTP_CONTENT_TYPE' => $representation->getContentType()),
            $representation->__toString()
        );

        $response = $dm->dispatch($request);

        $this->assertEquals(200, $response->getStatusCode());

        $putUser = $this->_em->find('DrestTests\Entities\Typical\User', $user->getId());

        $this->assertEquals($putEmail, $putUser->getEmailAddress());
    }

}
