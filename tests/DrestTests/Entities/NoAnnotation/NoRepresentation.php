<?php
namespace DrestTests\Entities\NoAnnotation;

use Doctrine\ORM\Mapping as ORM;
use Drest\Mapping\Annotation as Drest;

/**
 * NoRepresentation
 *
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