<?php

namespace Wapistrano\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Wapistrano\CoreBundle\Validator\Constraints as WapiAssert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Roles
 *
 * @ORM\Table(name="roles", indexes={@ORM\Index(name="index_roles_on_stage_id", columns={"stage_id"}), @ORM\Index(name="index_roles_on_host_id", columns={"host_id"})})
 * @ORM\Entity
 * @ExclusionPolicy("none")
 */
class Roles
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Exclude()
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var Stages
     *
     * @ORM\ManyToOne(targetEntity="Stages")
     * @ORM\JoinColumn(name="stage_id", referencedColumnName="id")
     */
    private $stage;

    /**
     * @var Hosts
     * @ORM\ManyToOne(targetEntity="Hosts", cascade={ "persist"})
     * @ORM\JoinColumn(name="host_id", referencedColumnName="id")
     */
    private $host;

    /**
     * @var boolean
     *
     * @ORM\Column(name="`primary`", type="boolean", nullable=true)
     */
    private $primary;

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
     * @var boolean
     *
     * @ORM\Column(name="no_release", type="boolean", nullable=true)
     */
    private $noRelease;

    /**
     * @var integer
     *
     * @ORM\Column(name="ssh_port", type="integer", nullable=true)
     */
    private $sshPort;

    /**
     * @var boolean
     *
     * @ORM\Column(name="no_symlink", type="boolean", nullable=true)
     */
    private $noSymlink;



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
     * Set name
     *
     * @param string $name
     * @return Roles
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set stage
     *
     * @param Stages $stage
     * @return Roles
     */
    public function setStage($stage)
    {
        $this->stage = $stage;

        return $this;
    }

    /**
     * Get stage
     *
     * @return Stages
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Set host
     *
     * @param Hosts $host
     * @return Roles
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return Hosts
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set primary
     *
     * @param integer $primary
     * @return Roles
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;

        return $this;
    }

    /**
     * Get primary
     *
     * @return integer 
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Roles
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
     * @return Roles
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
     * Set noRelease
     *
     * @param integer $noRelease
     * @return Roles
     */
    public function setNoRelease($noRelease)
    {
        $this->noRelease = $noRelease;

        return $this;
    }

    /**
     * Get noRelease
     *
     * @return integer 
     */
    public function getNoRelease()
    {
        return $this->noRelease;
    }

    /**
     * Set sshPort
     *
     * @param integer $sshPort
     * @return Roles
     */
    public function setSshPort($sshPort)
    {
        $this->sshPort = $sshPort;

        return $this;
    }

    /**
     * Get sshPort
     *
     * @return integer 
     */
    public function getSshPort()
    {
        return $this->sshPort;
    }

    /**
     * Set noSymlink
     *
     * @param integer $noSymlink
     * @return Roles
     */
    public function setNoSymlink($noSymlink)
    {
        $this->noSymlink = $noSymlink;

        return $this;
    }

    /**
     * Get noSymlink
     *
     * @return integer 
     */
    public function getNoSymlink()
    {
        return $this->noSymlink;
    }
}
