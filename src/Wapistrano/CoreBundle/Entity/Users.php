<?php

namespace Wapistrano\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Wapistrano\ProfileBundle\Security\WapistranoPasswordEncoder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Users
 *
 * @ORM\Table(name="users", indexes={@ORM\Index(name="index_users_on_disabled", columns={"disabled"})})
 * @ORM\Entity(repositoryClass="Wapistrano\CoreBundle\Entity\UsersRepository")
 */
class Users implements UserInterface, \Serializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=255, nullable=true)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\Email(
     *     message = "'{{ value }}' is not a correct email address.",
     *     checkMX = true
     * )
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="crypted_password", type="string", length=255, nullable=true)
     */
    private $cryptedPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=255, nullable=true)
     */
    private $salt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="remember_token", type="string", length=255, nullable=true)
     */
    private $rememberToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="remember_token_expires_at", type="datetime", nullable=true)
     */
    private $rememberTokenExpiresAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="admin", type="integer", nullable=true)
     */
    private $admin = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="time_zone", type="string", length=255, nullable=true)
     */
    private $timeZone = 'UTC';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="disabled", type="datetime", nullable=true)
     */
    private $disabled;

    /**
     * @var integer
     * @ORM\ManyToMany(targetEntity="Projects", inversedBy="user")
     * @ORM\JoinTable(name="projects_users",
     *      joinColumns={@ORM\JoinColumn(name="users_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="projects_id", referencedColumnName="id")}
     *      ))
     * @ORM\OrderBy({"name"="ASC"})
     */
    private $project;



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set login
     *
     * @param string $login
     * @return Users
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string 
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Users
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set cryptedPassword
     *
     * @param string $cryptedPassword
     * @return Users
     */
    public function setCryptedPassword($cryptedPassword)
    {
        if("" != $cryptedPassword){
            $encrypt = new WapistranoPasswordEncoder();
            $this->cryptedPassword = $encrypt->encodePassword($cryptedPassword, $this->getSalt());
        }

        return $this;
    }

    /**
     * Get cryptedPassword
     *
     * @return string 
     */
    public function getCryptedPassword()
    {
        return $this->cryptedPassword;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return Users
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        if("" == $this->salt) {
            $encrypt = new WapistranoPasswordEncoder();
            $this->setSalt($encrypt->genSalt());
        }
        return $this->salt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Users
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Users
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set rememberToken
     *
     * @param string $rememberToken
     * @return Users
     */
    public function setRememberToken($rememberToken)
    {
        $this->rememberToken = $rememberToken;

        return $this;
    }

    /**
     * Get rememberToken
     *
     * @return string 
     */
    public function getRememberToken()
    {
        return $this->rememberToken;
    }

    /**
     * Set rememberTokenExpiresAt
     *
     * @param \DateTime $rememberTokenExpiresAt
     * @return Users
     */
    public function setRememberTokenExpiresAt($rememberTokenExpiresAt)
    {
        $this->rememberTokenExpiresAt = $rememberTokenExpiresAt;

        return $this;
    }

    /**
     * Get rememberTokenExpiresAt
     *
     * @return \DateTime 
     */
    public function getRememberTokenExpiresAt()
    {
        return $this->rememberTokenExpiresAt;
    }

    /**
     * Set admin
     *
     * @param integer $admin
     * @return Users
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin
     *
     * @return integer 
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set timeZone
     *
     * @param string $timeZone
     * @return Users
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    /**
     * Get timeZone
     *
     * @return string 
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * Set disabled
     *
     * @param \DateTime $disabled
     * @return Users
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Get disabled
     *
     * @return \DateTime 
     */
    public function getDisabled()
    {
        return $this->disabled;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->project = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Add project
     *
     * @param \Wapistrano\CoreBundle\Entity\Projects $project
     * @return Users
     */
    public function addProject(\Wapistrano\CoreBundle\Entity\Projects $project)
    {
        if (!$this->project->contains($project)) {
            $this->project[] = $project;
        }

        return $this;
    }

    /**
     * Remove project
     *
     * @param \Wapistrano\CoreBundle\Entity\Projects $project
     */
    public function removeProject(\Wapistrano\CoreBundle\Entity\Projects $project)
    {
        $this->project->removeElement($project);
    }

    /**
     * Get project
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProject()
    {
        return $this->project;
    }

    /*** SYMFONY USER INTERFACE IMPLEMENTATION ***/

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(array($this->id, $this->email));
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list($this->id,$this->email) = unserialize($serialized);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return array|\Symfony\Component\Security\Core\Role\Role[]
     */
    public function getRoles()
    {
        if($this->getAdmin() == 1) {
            return array('ROLE_ADMIN');
        } else {
            return array('ROLE_CLIENT');
        }

    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Users
     */
    public function setPassword($password)
    {
        $encrypt = new WapistranoPasswordEncoder();
        $this->cryptedPassword = $encrypt->encodePassword($password, $this->getSalt());

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->cryptedPassword;
    }

    /**
     * logout
     */
    public function eraseCredentials()
    {

    }
}
