<?php
namespace Entities;

// uniqueConstraints={@UniqueConstraint(name="api_key_idx", columns={"api_key"})})
use Drest\Mapping\Annotation as Drest;
use Doctrine\ORM\Mapping as ORM;

// Alternative
//  *      		expose={"username", "email_address", "profile" : {"id", "lastname", "addresses" : {"address"}}, "phone_numbers" : {"number"}}
// Use short expose syntax in http headers / request params:  username|email_address|profile[id|lastname|addresses[id]]|phone_numbers
/**
 * User
 * @Drest\Resource(
 * 		writers={"Json", "Xml"},
 *      routes={
 *      	@Drest\Route(
 *      		name="get_user",
 *      		route_pattern="/user/:id+",
 *      		route_conditions={"id": "\d+"},
 *      		verbs={"GET"},
 *      		content="element"
 *      	),
 *          @Drest\Route(name="post_user", route_pattern="/user", verbs={"POST"}, call_method="addUser", content="element"),
 *          @Drest\Route(name="update_user", route_pattern="/user/:id+", route_conditions={"id": "\d+"}, verbs={"PUT"}, content="element"),
 *          @Drest\Route(name="get_users", route_pattern="/users", verbs={"GET"}, content="collection")
 *      }
 * )
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User
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
     * @ORM\OneToOne(targetEntity="Profile", mappedBy="user", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $profile;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $phone_numbers
     * @ORM\OneToMany(targetEntity="PhoneNumber", mappedBy="user", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $phone_numbers;

    /**
     * @var string $username
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @var string $email_address
     * @ORM\Column(name="email_address", type="string", length=255)
     */
    private $email_address;


    public function __construct()
    {
        $this->phone_numbers = new \Doctrine\Common\Collections\ArrayCollection();
    }
}