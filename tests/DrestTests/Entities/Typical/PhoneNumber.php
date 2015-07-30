<?php
namespace DrestTests\Entities\Typical;

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
     * @var \DrestTests\Entities\Typical\User $user
     * @ORM\ManyToOne(targetEntity="User", inversedBy="phone_numbers", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var string $number
     * @ORM\Column(name="number", type="bigint")
     */
    private $number;

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }
}
