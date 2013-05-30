<?php
namespace Entities;

// uniqueConstraints={@UniqueConstraint(name="api_key_idx", columns={"api_key"})})
use Drest\Request;

use Drest\Mapping\Annotation as Drest;
use Doctrine\ORM\Mapping as ORM;

// Alternative
//  *      		expose={"username", "email_address", "profile" : {"id", "lastname", "addresses" : {"address"}}, "phone_numbers" : {"number"}}
// Use short expose syntax in http headers / request params:  username|email_address|profile[id|lastname|addresses[id]]|phone_numbers
// service_call={"Service\User", "getMyCustomElement"}
/**
 * User
 * @Drest\Resource(
 * 		representations={"Json", "Xml"},
 *      routes={
 *      	@Drest\Route(
 *      		name="get_user",
 *      		routePattern="/user/:id",
 *      		routeConditions={"id": "\d+"},
 *      		verbs={"GET"},
 *      		expose={"username", "email_address", "profile", "phone_numbers"}
 *      	),
 *      	@Drest\Route(name="get_user_profile", routePattern="/user/:id/profile", verbs={"GET"}, expose={"profile"}),
 *      	@Drest\Route(name="get_user_numbers", routePattern="/user/:id/numbers", verbs={"GET"}, expose={"phone_numbers"}),
 *          @Drest\Route(name="post_user", routePattern="/user", verbs={"POST"}, expose={"username", "email_address", "profile" : {"firstname", "lastname"}, "phone_numbers" : {"number"}}),
 *          @Drest\Route(name="update_user", routePattern="/user/:id+", routeConditions={"id": "\d+"}, verbs={"PUT"}, expose={"email_address", "profile", "phone_numbers"}),
 *          @Drest\Route(name="get_users", routePattern="/users", verbs={"GET"}, collection=true, expose={"username", "email_address", "profile", "phone_numbers"})
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

    /**
     * Get the Id
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @Drest\Handle(for="post_user")
     */
    public function populatePost(array $data)
    {
        if (isset($data['email_address']))
        {
            $this->email_address = $data['email_address'];
        }
        if (isset($data['username']))
        {
            $this->username = $data['username'];
        }
    }


}