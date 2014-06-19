<?php
namespace DrestTests\Entities\CMS;

// uniqueConstraints={@UniqueConstraint(name="api_key_idx", columns={"api_key"})})

// Alternative
//  *      		expose={"username", "email_address", "profile" : {"id", "lastname", "addresses" : {"address"}}, "phone_numbers" : {"number"}}
// Use short expose syntax in http headers / request params:  username|email_address|profile[id|lastname|addresses[id]]|phone_numbers
// service_call={"Service\User", "getMyCustomElement"}
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Drest\Mapping\Annotation as Drest;

/**
 * User
 *
 * @Drest\Resource(
 *        representations={"Json", "Xml"},
 *      routes={
 *      	@Drest\Route(
 *            name="get_user",
 *            routePattern="/user/:id",
 *            routeConditions={"id": "\d+"},
 *            verbs={"GET"}
 *        ),
 *      	@Drest\Route(name="get_user_profile", routePattern="/user/:id/profile", verbs={"GET"}, expose={"profile"}),
 *      	@Drest\Route(name="get_user_numbers", routePattern="/user/:id/numbers", verbs={"GET"}, expose={"phone_numbers"}),
 *          @Drest\Route(name="post_user", routePattern="/user", verbs={"POST"}, expose={"username", "email_address", "profile" : {"firstname", "lastname"}, "phone_numbers" : {"number"}}),
 *          @Drest\Route(name="get_users", routePattern="/users", verbs={"GET"}, collection=true, expose={"username", "email_address", "profile", "phone_numbers"}),
 *          @Drest\Route(name="update_user", routePattern="/user/:id", verbs={"PUT", "PATCH"}, expose={"username", "email_address", "profile" : {"firstname", "lastname"}}),
 *          @Drest\Route(name="delete_user", routePattern="/user/:id", verbs={"DELETE"}),
 *          @Drest\Route(name="delete_users", routePattern="/users", collection=true, verbs={"DELETE"})
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
     * @var \Entities\Profile $profile
     * @ORM\OneToOne(targetEntity="Profile", mappedBy="user", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $profile;

    /**
     * @var ArrayCollection $phone_numbers
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
        $this->phone_numbers = new ArrayCollection();
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
     * @Drest\Handle(for="post_user", injectRequest=true)
     */
    public function populatePost(array $data, \DrestCommon\Request\Request $request)
    {
        if (isset($data['email_address'])) {
            $this->email_address = $data['email_address'];
        }
        if (isset($data['username'])) {
            $this->username = $data['username'];
        }
        if (isset($data['phone_numbers']) && is_array($data['phone_numbers'])) {
            foreach ($data['phone_numbers'] as $phone_number) {
                $pn = new PhoneNumber();
                $pn->setNumber($phone_number['number']);
                $this->addPhoneNumber($pn);
            }
        }
    }

    /**
     * Add a phone number
     * @param PhoneNumber $phoneNumber
     */
    public function addPhoneNumber(PhoneNumber $phoneNumber)
    {
        $phoneNumber->setUser($this);
        $this->phone_numbers->add($phoneNumber);
    }

    /**
     * Get phone numbers
     * @return ArrayCollection $phone_numbers
     */
    public function getPhoneNumbers()
    {
        return $this->phone_numbers;
    }

    /**
     * Set the email address
     * @param string $email_address
     */
    public function setEmailAddress($email_address)
    {
        $this->email_address = $email_address;
    }

    /**
     * @return string $email_address
     */
    public function getEmailAddress()
    {
        return $this->email_address;
    }

    /**
     * Get the username
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the username
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @Drest\Handle(for="update_user")
     */
    public function patchUser(array $data)
    {
        if (isset($data['email_address'])) {
            $this->email_address = $data['email_address'];
        }
        if (isset($data['username'])) {
            $this->username = $data['username'];
        }
    }

}
