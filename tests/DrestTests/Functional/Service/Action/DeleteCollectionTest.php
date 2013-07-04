<?php
namespace DrestTests\Functional\Service\Action;

use DrestTests\DrestFunctionalTestCase;

class DeleteCollectionTests extends DrestFunctionalTestCase
{

    public function testDeleteCollectionRequest()
    {
        $dm = $this->_getDrestManager($this->_em);
        $representation = new \Drest\Representation\Json();

        $users = array(
            array('email_address' => 'frodo.baggin@theshire.com', 'username' => 'frodo.baggins'),
            array('email_address' => 'samwise.gamgee@theshire.com', 'username' => 'samwise.gamgee')
        );

        foreach ($users as $user)
        {
            $userObj = new \DrestTests\Entities\CMS\User();
            $userObj->setEmailAddress($user['email_address']);
            $userObj->setUsername($user['username']);

            $this->_em->persist($userObj);
            $this->_em->flush();

            $this->_em->refresh($userObj);

            $entity = $this->_em->getRepository('DrestTests\Entities\CMS\User')->find($userObj->getId());
            $this->assertEquals($userObj, $entity);
        }

        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/users',
            'DELETE',
            array(),
            array(),
            array(),
            array('HTTP_ACCEPT' => $representation->getContentType())
        );

        $response = $dm->dispatch($request);

        $this->assertEquals(200, $response->getStatusCode());

        $deletedEntities = $this->_em->getRepository('DrestTests\Entities\CMS\User')->findAll();
        $this->assertCount(0, $deletedEntities);
    }

}