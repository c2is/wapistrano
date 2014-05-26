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
    private $logger;
    private $redis;
    private $jobHandle;
    private $terminateStatus;

    public function __construct($config, $logger)
    {
        parent::__construct();
        $this->addServer($config["gearman"]["host"], $config["gearman"]["port"]);

        $this->redis = new \Redis();
        $this->redis->connect("127.0.0.1", 6379);

        $this->logger = $logger;
    }

    public function init() {
        $this->jobHandle = "";
        $this->terminateStatus = "";
    }

    public function doBackgroundAsync($function, $workload) {
        $this->init();
        $status = false;

        $jobId = $this->doBackground($function, $workload);

        if ($this->returnCode() == GEARMAN_SUCCESS)
        {
            $status = $jobId;
        }

        $this->jobHandle = $jobId;

        if($status) {
            return $this->terminate($jobId);
        } else {
            $this->terminateStatus = "error";
            return $this;
        }
    }

    public function doBackgroundSync($function, $workload) {
        $this->init();
        $status = false;

        $job_handle = $this->doBackground($function, $workload);

        if ($this->returnCode() == GEARMAN_SUCCESS)
        {
            $status = true;
            $this->jobHandle = $job_handle;

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

        if($status) {
            return $this->terminate($job_handle);
        } else {
            $this->terminateStatus = "error";
            return $this;
        }

    }

    private function terminate($job_handle) {
        $redisJobLog = $this->redis->get($job_handle);
        // if log hasn't been deleted by python worker, an exception occured
        if($redisJobLog) {
            $this->redis->del($job_handle);
            $this->logger->error("Job error log: ". $redisJobLog);
            $this->terminateStatus = "error";
        } else {
            $this->logger->info("Job log: ". $this->redis->get($job_handle));
            $this->terminateStatus = "success";
        }

        return $this;
    }

    public function getLog($jobHandle) {
        $redisJobLog = $this->redis->get($jobHandle);

        return $redisJobLog;
    }

    /**
     * @return mixed
     */
    public function getTerminateStatus()
    {
        return $this->terminateStatus;
    }

    /**
     * @return mixed
     */
    public function getJobHandle()
    {
        return $this->jobHandle;
    }



}
