<?php
namespace DrestTests\Entities\HandleAlreadyDefined;

use Drest\Mapping\Annotation as Drest;

/**
 * HandleAlreadyDefined
 *
 * @Drest\Resource(
 *
 *  @Drest\Route(
 *      name="get_user",
 *      routePattern="/user/:id",
 *      verbs={"GET"}
 *  )
 * )
 *
 */
class HandleAlreadyDefined
{
    /**
     * @Drest\Handle(for="get_user")
     */
    public function getUser(array $data)
    {
    }

    /**
     * @Drest\Handle(for="get_user")
     */
    public function getUserAgain(array $data)
    {
    }
}