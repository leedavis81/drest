<?php
namespace Entities;

use Doctrine\ORM\Mapping as ORM;


/**
 * Profile
 *
 * @ORM\Table(name="address")
 * @ORM\Entity
 */
class Address
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
     * @var Entities\Profile $profile
     * @ORM\ManyToOne(targetEntity="Profile", inversedBy="addresses", fetch="EAGER")
     */
    private $profile;

    /**
     * @var test $number
     * @ORM\Column(name="address", type="string")
     */
    private $address;
}