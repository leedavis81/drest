<?php
namespace DrestTests\Entities\NoServiceDefinition;

use Drest\Mapping\Annotation as Drest;

/**
 * NoServiceDefinition
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
class NoServiceDefinition
{
}