<?php

namespace Wapistrano\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stages
 *
 * @ORM\Table(name="stages", indexes={@ORM\Index(name="index_stages_on_project_id", columns={"project_id"})})
 * @ORM\Entity
 */
class Stages
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
     * @ORM\Column(name="project_id", type="integer", nullable=true)
     */
    private $projectId;

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
     * @ORM\Column(name="alert_emails", type="text", nullable=true)
     */
    private $alertEmails;

    /**
     * @var integer
     *
     * @ORM\Column(name="locked_by_deployment_id", type="integer", nullable=true)
     */
    private $lockedByDeploymentId;

    /**
     * @var integer
     *
     * @ORM\Column(name="locked", type="integer", nullable=true)
     */
    private $locked = '0';

    /**
     * @var integer
     * @ORM\ManyToMany(targetEntity="Recipes", inversedBy="stage", cascade={"persist", "merge"})
     * @ORM\JoinTable(name="recipes_stages",
     *      joinColumns={@ORM\JoinColumn(name="stages_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="recipes_id", referencedColumnName="id")}
     *      ))
     */
    protected $recipe;



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
     * @return Stages
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
     * Set projectId
     *
     * @param integer $projectId
     * @return Stages
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;

        return $this;
    }

    /**
     * Get projectId
     *
     * @return integer 
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Stages
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
     * @return Stages
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
     * Set alertEmails
     *
     * @param string $alertEmails
     * @return Stages
     */
    public function setAlertEmails($alertEmails)
    {
        $this->alertEmails = $alertEmails;

        return $this;
    }

    /**
     * Get alertEmails
     *
     * @return string 
     */
    public function getAlertEmails()
    {
        return $this->alertEmails;
    }

    /**
     * Set lockedByDeploymentId
     *
     * @param integer $lockedByDeploymentId
     * @return Stages
     */
    public function setLockedByDeploymentId($lockedByDeploymentId)
    {
        $this->lockedByDeploymentId = $lockedByDeploymentId;

        return $this;
    }

    /**
     * Get lockedByDeploymentId
     *
     * @return integer 
     */
    public function getLockedByDeploymentId()
    {
        return $this->lockedByDeploymentId;
    }

    /**
     * Set locked
     *
     * @param integer $locked
     * @return Stages
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     *
     * @return integer 
     */
    public function getLocked()
    {
        return $this->locked;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->recipe = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add recipe
     *
     * @param \Wapistrano\CoreBundle\Entity\Recipes $recipe
     * @return Stages
     */
    public function addRecipe(\Wapistrano\CoreBundle\Entity\Recipes $recipe)
    {
        $this->recipe[] = $recipe;

        return $this;
    }

    /**
     * Remove recipe
     *
     * @param \Wapistrano\CoreBundle\Entity\Recipes $recipe
     */
    public function removeRecipe(\Wapistrano\CoreBundle\Entity\Recipes $recipe)
    {
        $this->recipe->removeElement($recipe);
    }

    /**
     * Get recipe
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecipe()
    {
        return $this->recipe;
    }

    /**
     * Set recipe
     *
     * @param Array
     */
    public function setRecipe(Array $recipe)
    {
        $this->recipe = $recipe;
    }
}
