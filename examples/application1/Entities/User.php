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
 *      		verbs={"GET"}
 *      	),
 *      	@Drest\Route(name="get_user_profile", routePattern="/user/:id/profile", verbs={"GET"}, expose={"profile"}),
 *      	@Drest\Route(name="get_user_numbers", routePattern="/user/:id/numbers", verbs={"GET"}, expose={"phone_numbers"}),
 *          @Drest\Route(name="post_user", routePattern="/user", verbs={"POST"}),
 *          @Drest\Route(name="update_user", routePattern="/user/:id+", routeConditions={"id": "\d+"}, verbs={"PUT"}),
 *          @Drest\Route(name="get_users", routePattern="/users", verbs={"GET"}, collection=true)
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
     * @Drest\Handle(for="post_user")
     */
    public function populatePost(Request $request)
    {
        $this->email_address = $request->getPost('email_address');
        $this->username = $request->getPost('username');
    }


}