<?php
namespace DrestTests\Entities\EmptyRouteName;

use Drest\Mapping\Annotation as Drest;

/**
 * EmptyRouteName
 *
 * @Drest\Resource(
 *  @Drest\Route(
 *      name="",
 *      routePattern="/user/:id",
 *      verbs={"GET"}
 *  ),
 * )
 *
 */
class EmptyRouteName
{
}