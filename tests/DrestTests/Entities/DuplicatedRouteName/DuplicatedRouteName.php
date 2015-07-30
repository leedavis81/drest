<?php
namespace DrestTests\Entities\DuplicatedRouteName;

use Drest\Mapping\Annotation as Drest;

/**
 * DuplicatedRouteName
 *
 * @Drest\Resource(
 *
 *  @Drest\Route(
 *      name="get_user",
 *      routePattern="/user/:id",
 *      verbs={"GET"}
 *  ),
 *  @Drest\Route(
 *      name="get_user",
 *      routePattern="/users/:id",
 *      verbs={"GET"}
 *  )
 * )
 *
 */
class DuplicatedRouteName
{
}