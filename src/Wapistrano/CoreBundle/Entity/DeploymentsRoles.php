<?php

namespace Wapistrano\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DeploymentsRoles
 *
 * @ORM\Table(name="deployments_roles")
 * @ORM\Entity
 */
class DeploymentsRoles
{
    /**
     * @var integer
     *
     * @ORM\Column(name="deployment_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $deploymentId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="role_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $roleId = '0';



    /**
     * Set deploymentId
     *
     * @param integer $deploymentId
     * @return DeploymentsRoles
     */
    public function setDeploymentId($deploymentId)
    {
        $this->deploymentId = $deploymentId;

        return $this;
    }

    /**
     * Get deploymentId
     *
     * @return integer 
     */
    public function getDeploymentId()
    {
        return $this->deploymentId;
    }

    /**
     * Set roleId
     *
     * @param integer $roleId
     * @return DeploymentsRoles
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * Get roleId
     *
     * @return integer 
     */
    public function getRoleId()
    {
        return $this->roleId;
    }
}
