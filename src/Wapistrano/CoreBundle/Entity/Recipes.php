<?php

namespace Wapistrano\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Recipes
 *
 * @ORM\Table(name="recipes")
 * @ORM\Entity
 * @ExclusionPolicy("none")
 */
class Recipes
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
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text", nullable=true)
     */
    private $body;

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
     * @ORM\ManyToMany(targetEntity="Stages", mappedBy="recipe")
     * @Exclude()
     */
    private $stage;


    /**
     * @var integer
     *
     * @ORM\Column(name="version", type="integer", nullable=true)
     */
    private $version = '1';



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
     * @return Recipes
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
     * Set description
     *
     * @param string $description
     * @return Recipes
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Recipes
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Recipes
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
     * @return Recipes
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
     * Set version
     *
     * @param integer $version
     * @return Recipes
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return integer 
     */
    public function getVersion()
    {
        return $this->version;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->stage = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add stage
     *
     * @param \Wapistrano\CoreBundle\Entity\Stages $stage
     * @return Recipes
     */
    public function addStage(\Wapistrano\CoreBundle\Entity\Stages $stage)
    {
        $this->stage[] = $stage;

        return $this;
    }

    /**
     * Remove stage
     *
     * @param \Wapistrano\CoreBundle\Entity\Stages $stage
     */
    public function removeStage(\Wapistrano\CoreBundle\Entity\Stages $stage)
    {
        $this->stage->removeElement($stage);
    }

    /**
     * Get stage
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStage()
    {
        return $this->stage;
    }
}
