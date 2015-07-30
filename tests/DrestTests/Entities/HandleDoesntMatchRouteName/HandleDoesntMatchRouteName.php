<?php
namespace DrestTests\Entities\HandleDoesntMatchRouteName;

use Drest\Mapping\Annotation as Drest;

/**
 * HandleDoesntMatchRouteName
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
class HandleDoesntMatchRouteName
{
    /**
     * @Drest\Handle(for="no_idea")
     */
    public function getUser(array $data)
    {
    }

}