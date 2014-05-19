<?php

namespace Wapistrano\CoreBundle\Broker;

use Wapistrano\CoreBundle\Entity\Stages;
use Wapistrano\CoreBundle\Entity\Roles;
use Wapistrano\CoreBundle\Entity\Hosts;
use Wapistrano\CoreBundle\Form\RolesTypeAdd;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class Gearman extends \GearmanClient
{

    public function __construct($config)
    {
        parent::__construct();
        $this->addServer($config["gearman"]["host"], $config["gearman"]["port"]);
    }

    public function doBackgroundAsync($function, $workload) {
        $status = false;

        $jobId = $this->doBackground($function, $workload);

        if ($this->returnCode() == GEARMAN_SUCCESS)
        {
            $status = $jobId;
        }

        return $status;
    }

    public function doBackgroundSync($function, $workload) {
        $status = false;

        $job_handle = $this->doBackground($function, $workload);

        if ($this->returnCode() == GEARMAN_SUCCESS)
        {
            $status = true;

            $done = false;
            do
            {
                usleep(1000);
                $stat = $this->jobStatus($job_handle);
                if (!$stat[0]) // the job is unknown so it is done
                    $done = true;
            }
            while(!$done);
        }

        return $status;
    }

}
