<?php
namespace DrestTests\Entities;


use Drest\Mapping\Annotation as Drest;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="phone_number")
 * @ORM\Entity
 */
class PhoneNumber
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", length=4)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Entities\User $user
     * @ORM\ManyToOne(targetEntity="User", inversedBy="phone_numbers", fetch="EAGER")
     */
    private $user;

    /**
     * @var string $number
     * @ORM\Column(name="number", type="integer", length=255)
     */
    private $number;

}