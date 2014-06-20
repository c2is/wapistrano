<?php

namespace Wapistrano\CoreBundle\Collector;

use Wapistrano\CoreBundle\Entity\Stages;
use Wapistrano\CoreBundle\Entity\Roles;
use Wapistrano\CoreBundle\Entity\Hosts;
use Wapistrano\CoreBundle\Form\RolesTypeAdd;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class Collector
{
    private $em;
    private $gearman;

    public function __construct($em, $gearman)
    {
        $this->em = $em;
        $this->gearman = $gearman;
    }

    /*
     * Check deployment status in redis and update database according to that
     */
    public function updateDeploymentsStatus()
    {
        $repoDeployments  = $this->em->getRepository('WapistranoCoreBundle:Deployments');

        $runningDeployments = $repoDeployments->findBy(array("status" => "running"));

        $i = 0;
        foreach ($runningDeployments as $deploy) {
            $jobLog = $this->gearman->getLog($deploy->getJobHandle());

            if (false !== strpos($jobLog, "Wapistrano job ended")) {
                $this->gearman->delRedisLog($deploy->getJobHandle());
                $deploy->setStatus("success");
                $this->em->persist($deploy);
                $i++;
            } elseif (false !== strpos($jobLog, "Wapistrano Job failed")) {
                $this->gearman->delRedisLog($deploy->getJobHandle());
                $deploy->setStatus("failed");
                $this->em->persist($deploy);
                $i++;
            }
            $this->em->flush();
        }

        return $i;
    }
}

