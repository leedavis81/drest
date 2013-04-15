<?php
namespace Entities;

// uniqueConstraints={@UniqueConstraint(name="api_key_idx", columns={"api_key"})})
use Drest\Mapping\Annotation as Drest;
use Doctrine\ORM\Mapping as ORM;

// Alternative


/**
 * User
 * @Drest\Resource(
 * 		writers={"Xml", "Json"},
 *      services={
 *      	@Drest\Service(name="get_user", route_pattern="/user/:id", route_conditions={"id": "\d*"}, verbs={"GET"}, content="element"),
 *          @Drest\Service(name="post_user", route_pattern="/user", verbs={"GET"}, repository_method="addUser", content="element"),
 *          @Drest\Service(name="get_users", route_pattern="/users", verbs={"GET"}, repository_method="getUsers", content="collection"),
 *      }
 * )
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Drest\Repository")
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
     * @var string $username
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @var string $email_address
     * @ORM\Column(name="email_address", type="string", length=255)
     */
    private $email_address;

    /**
     * Get id
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     * @return string $username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email_address
     * @param string $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->email_address = $emailAddress;
    }

    /**
     * Get email_address
     * @return string $emailAddress
     */
    public function getEmailAddress()
    {
        return $this->email_address;
    }

}