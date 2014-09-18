<?php

namespace Wapistrano\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\XmlRoot;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\XmlList;

/**
 * Projects
 *
 * @ORM\Table(name="projects")
 * @ORM\Entity
 * @ExclusionPolicy("none")
 * @XmlRoot("project")
 */
class Projects
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
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

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
     * @var @var string
     *
     * @ORM\Column(name="template", type="text", nullable=true, options={"comment" = "Legacy field"})
     */
    private $template;

    /**
     * @var integer
     *
     * @ORM\ManyToMany(targetEntity="Users", mappedBy="project")
     */
    private $user;

    /**
     * @var Stages
     *
     * @ORM\OneToMany(targetEntity="Stages", mappedBy="project", cascade={"remove"})
     * @XmlList(entry = "stage")
     */
    private $stages;

    /**
     * @var ConfigurationParameters
     *
     * @XmlList(entry = "configuration")
     */
    private $configurationParameters;

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
     * @return Projects
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
     * @return Projects
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Projects
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
     * @return Projects
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
     * Set template
     *
     * @param string $template
     * @return Projects
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add user
     *
     * @param \Wapistrano\CoreBundle\Entity\Users $user
     * @return Projects
     */
    public function addUser(\Wapistrano\CoreBundle\Entity\Users $user)
    {

        if (!$this->user->contains($user)) {
            $this->user->add($user);
            if (!$user->getProject()->contains($this)) {
                $user->addProject($this);
            }
        }

        return $this;
    }

    /**
     * Remove user
     *
     * @param \Wapistrano\CoreBundle\Entity\Users $user
     */
    public function removeUser(\Wapistrano\CoreBundle\Entity\Users $user)
    {
        $this->user->removeElement($user);
    }

    /**
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add stages
     *
     * @param \Wapistrano\CoreBundle\Entity\Stages $stages
     * @return Projects
     */
    public function addStage(\Wapistrano\CoreBundle\Entity\Stages $stages)
    {
        $this->stages[] = $stages;

        return $this;
    }

    /**
     * Remove stages
     *
     * @param \Wapistrano\CoreBundle\Entity\Stages $stages
     */
    public function removeStage(\Wapistrano\CoreBundle\Entity\Stages $stages)
    {
        $this->stages->removeElement($stages);
    }

    /**
     * Get stages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStages()
    {
        return $this->stages;
    }

    /**
     * Set configurationParameters
     *
     * @param \Wapistrano\CoreBundle\Entity\ConfigurationParameters $configurationParameters
     * @return Projects
     */
    public function setConfigurationParameters($configurationParameters)
    {
       $this->configurationParameters = $configurationParameters;
        return $this;
    }

}
