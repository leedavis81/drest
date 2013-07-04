<?php
namespace DrestTests\Functional\Service\Action;

use DrestTests\DrestFunctionalTestCase;

class DeleteElementTests extends DrestFunctionalTestCase
{

    public function testEmptyRequest()
    {
        $dm = $this->_getDrestManager();

        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/user/1',
            'DELETE'
        );
        $response = $dm->dispatch($request);
        $this->assertEquals(404, $response->getStatusCode());
    }


    /**
     * @expectedException \Doctrine\ORM\ORMException
     */
    public function testDeleteElementRequest()
    {
        $dm = $this->_getDrestManager($this->_em);
        $representation = new \Drest\Representation\Json();

        $user = new \DrestTests\Entities\CMS\User();

        $email = 'frodo.baggin@theshire.com';
        $username = 'frodo.baggins';
        $user->setEmailAddress($email);
        $user->setUsername($username);

        $this->_em->persist($user);
        $this->_em->flush();
        $this->_em->refresh($user);

        // Ensure they've been written to storage
        $entity = $this->_em->getRepository('DrestTests\Entities\CMS\User')->find($user->getId());
        $this->assertEquals($entity, $user);

        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/user/' . $user->getId(),
            'DELETE',
            array(),
            array(),
            array(),
            array('HTTP_ACCEPT' => $representation->getContentType())
        );

        $response = $dm->dispatch($request);

        $this->assertEquals(200, $response->getStatusCode());

        $deletedEntity = $this->_em->getRepository('DrestTests\Entities\CMS\User')->find($user->getId());
        $this->assertNull($deletedEntity);
    }

}