<?php

namespace Wapistrano\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Roles
 *
 * @ORM\Table(name="roles", indexes={@ORM\Index(name="index_roles_on_stage_id", columns={"stage_id"}), @ORM\Index(name="index_roles_on_host_id", columns={"host_id"})})
 * @ORM\Entity
 */
class Roles
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="stage_id", type="integer", nullable=true)
     */
    private $stageId;

    /**
     * @var integer
     *
     * @ORM\Column(name="host_id", type="integer", nullable=true)
     */
    private $hostId;

    /**
     * @var integer
     *
     * @ORM\Column(name="primary", type="integer", nullable=true)
     */
    private $primary = '0';

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
     * @var integer
     *
     * @ORM\Column(name="no_release", type="integer", nullable=true)
     */
    private $noRelease = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="ssh_port", type="integer", nullable=true)
     */
    private $sshPort;

    /**
     * @var integer
     *
     * @ORM\Column(name="no_symlink", type="integer", nullable=true)
     */
    private $noSymlink = '0';



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
     * Set stageId
     *
     * @param integer $stageId
     * @return Roles
     */
    public function setStageId($stageId)
    {
        $this->stageId = $stageId;

        return $this;
    }

    /**
     * Get stageId
     *
     * @return integer 
     */
    public function getStageId()
    {
        return $this->stageId;
    }

    /**
     * Set hostId
     *
     * @param integer $hostId
     * @return Roles
     */
    public function setHostId($hostId)
    {
        $this->hostId = $hostId;

        return $this;
    }

    /**
     * Get hostId
     *
     * @return integer 
     */
    public function getHostId()
    {
        return $this->hostId;
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
