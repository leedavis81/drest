<?php
namespace DrestTests\Functional\Service\Action;

use DrestTests\DrestFunctionalTestCase;

class DeleteCollectionTest extends DrestFunctionalTestCase
{

    public function testDeleteCollectionRequest()
    {
        $dm = $this->_getDrestManager($this->_em);
        $representation = new \DrestCommon\Representation\Json();

        $users = array(
            array('email_address' => 'frodo.baggin@theshire.com', 'username' => 'frodo.baggins'),
            array('email_address' => 'samwise.gamgee@theshire.com', 'username' => 'samwise.gamgee')
        );

        foreach ($users as $user) {
            $userObj = new \DrestTests\Entities\Typical\User();
            $userObj->setEmailAddress($user['email_address']);
            $userObj->setUsername($user['username']);

            $this->_em->persist($userObj);
            $this->_em->flush();

            $this->_em->refresh($userObj);

            $entity = $this->_em->getRepository('DrestTests\Entities\Typical\User')->find($userObj->getId());
            $this->assertEquals($userObj, $entity);
        }

        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/users',
            'DELETE',
            [],
            [],
            [],
            array('HTTP_ACCEPT' => $representation->getContentType())
        );

        $response = $dm->dispatch($request);

        $this->assertEquals(200, $response->getStatusCode());

        $deletedEntities = $this->_em->getRepository('DrestTests\Entities\Typical\User')->findAll();
        $this->assertCount(0, $deletedEntities);
    }


    public function testDeleteCollectionRequestOnParameter()
    {
        $dm = $this->_getDrestManager($this->_em);
        $representation = new \DrestCommon\Representation\Json();

        $users = array(
            array('email_address' => 'frodo.baggin@theshire.com', 'username' => 'same'),
            array('email_address' => 'samwise.gamgee@theshire.com', 'username' => 'same')
        );

        foreach ($users as $user) {
            $userObj = new \DrestTests\Entities\Typical\User();
            $userObj->setEmailAddress($user['email_address']);
            $userObj->setUsername($user['username']);

            $this->_em->persist($userObj);
            $this->_em->flush();

            $this->_em->refresh($userObj);

            $entity = $this->_em->getRepository('DrestTests\Entities\Typical\User')->find($userObj->getId());
            $this->assertEquals($userObj, $entity);
        }

        $this->assertCount(sizeof($users), $this->_em->getRepository('DrestTests\Entities\Typical\User')->findAll());

        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/users/username/same',
            'DELETE',
            [],
            [],
            [],
            array('HTTP_ACCEPT' => $representation->getContentType())
        );

        $response = $dm->dispatch($request);

        $this->assertEquals(200, $response->getStatusCode());

        $deletedEntities = $this->_em->getRepository('DrestTests\Entities\Typical\User')->findAll();
        $this->assertCount(0, $deletedEntities);
    }


    public function testDeleteCollectionRequestOnInvalidParameter()
    {
        $dm = $this->_getDrestManager($this->_em);

        $users = array(
            array('email_address' => 'frodo.baggin@theshire.com', 'username' => 'same'),
            array('email_address' => 'samwise.gamgee@theshire.com', 'username' => 'same')
        );

        foreach ($users as $user) {
            $userObj = new \DrestTests\Entities\Typical\User();
            $userObj->setEmailAddress($user['email_address']);
            $userObj->setUsername($user['username']);

            $this->_em->persist($userObj);
            $this->_em->flush();

            $this->_em->refresh($userObj);

            $entity = $this->_em->getRepository('DrestTests\Entities\Typical\User')->find($userObj->getId());
            $this->assertEquals($userObj, $entity);
        }

        $this->assertCount(sizeof($users), $this->_em->getRepository('DrestTests\Entities\Typical\User')->findAll());

        $response = $dm->dispatch(null, null, 'DrestTests\Entities\Typical\User::delete_users_by_username', ['wrongparam' => 'same']);

        // This SHOULD be a 500, currently getting 404 due to wrong service mapping. see https://github.com/leedavis81/drest/issues/16
        $this->assertEquals(500, $response->getStatusCode());

        // Should still have 2, as the above request would have failed
        $deletedEntities = $this->_em->getRepository('DrestTests\Entities\Typical\User')->findAll();
        $this->assertCount(2, $deletedEntities);
    }

}
