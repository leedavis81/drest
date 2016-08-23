<?php
namespace DrestTests\Entities\GetHandleUsed;

use Drest\Mapping\Annotation as Drest;

/**
 * GetHandleUsed
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
class GetHandleUsed
{
    /**
     * @Drest\Handle(for="get_user")
     */
    public static function getUser(array $data)
    {
        return [1, 2, 3];
    }
}