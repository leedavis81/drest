<?php
namespace DrestTests\Entities\InvalidVerbUsed;

use Drest\Mapping\Annotation as Drest;

/**
 * InvalidVerbUsed
 *
 * @Drest\Resource(
 *  @Drest\Route(
 *      name="junk_route",
 *      routePattern="/user/:id",
 *      verbs={"SOME_JUNK"}
 *  ),
 * )
 *
 */
class InvalidVerbUsed
{
}