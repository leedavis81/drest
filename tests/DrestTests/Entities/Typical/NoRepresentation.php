<?php
namespace DrestTests\Entities\Typical;

use Doctrine\ORM\Mapping as ORM;
use Drest\Mapping\Annotation as Drest;

/**
 * NoRepresentation
 *
 * @Drest\Resource(
 *      routes={
 *      	@Drest\Route(
 *            name="no_rep",
 *            routePattern="/no_rep/:id",
 *            verbs={"GET"}
 *        )
 *      }
 * )
 *
 * @ORM\Table(name="no_representation")
 * @ORM\Entity
 */
class NoRepresentation
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", length=4)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
}