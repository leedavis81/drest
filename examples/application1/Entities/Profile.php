<?php
namespace Entities;

use Doctrine\ORM\Mapping as ORM;
//use Drest\Mapping\Annotation as Drest;
// * @Drest\Resource(
// * 		writers={"Xml", "Json"},
// *      services={
// *      	@Drest\Service(name="get_profile", route_pattern="/profile/:id", verbs={"GET"}, repository_method="getProfile", content="element"),
// *          @Drest\Service(name="post_profile", route_pattern="/profile", verbs={"POST"}, repository_method="addProfile", content="element"),
// *          @Drest\Service(name="get_profiles", route_pattern="/profiles", verbs={"GET"}, repository_method="getProfiles", content="collection"),
// *      }
// * )



/**
 * Profile
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
     * @var Entities\User $admin_user
     * @ORM\OneToOne(targetEntity="User", inversedBy="profile", fetch="LAZY")
     */
    private $user;

    /**
     * @var string $username
     * @ORM\Column(name="username", type="string")
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

}