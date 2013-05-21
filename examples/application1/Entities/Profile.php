<?php
namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Drest\Mapping\Annotation as Drest;
// * @Drest\Resource(
// * 		representations={"Xml", "Json"},
// *      routes={
// *      	@Drest\Route(name="get_profile", routePattern="/profile/:id", verbs={"GET"}, content="element"),
// *          @Drest\Route(name="post_profile", routePattern="/profile", verbs={"POST"}, content="element"),
// *          @Drest\Route(name="get_profiles", routePattern="/profiles", verbs={"GET"}, content="collection"),
// *      }
// * )



/**
 * Profile
 *
 *
 *
 *
 * @ORM\Table(name="profile")
 * @ORM\Entity
 */
class Profile
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
     * @ORM\OneToOne(targetEntity="User", inversedBy="profile", fetch="LAZY")
     */
    private $user;

    /**
     * @var Doctrine\Common\Collections\ArrayCollection $addresses
     * @ORM\OneToMany(targetEntity="Address", mappedBy="profile", fetch="LAZY")
     */
    private $addresses;

    /**
     * @var string $title
     * @ORM\Column(name="title", type="string")
     */
    private $title;

    /**
     * @var string $firstname
     * @ORM\Column(name="firstname", type="string")
     */
    private $firstname;

    /**
     * @var string $lastname
     * @ORM\Column(name="lastname", type="string")
     */
    private $lastname;


    public function __construct()
    {
        $this->addresses = new \Doctrine\Common\Collections\ArrayCollection();
    }
}