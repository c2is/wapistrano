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
    public $timeOutForEndingJob; // in second

    private $brokerErrors = array();
    private $logger;
    private $redis;
    private $jobHandle;
    private $terminateStatus;

    private $startMicroTime;

    public function __construct($config, $logger)
    {

        parent::__construct();

        $this->logger = $logger;

        $numServersUnavailable = 0;
        foreach($config["gearman"] as $server) {
            try {
            $this->addServer($server["host"], $server["port"]);
            } catch(\Exception $e) {
                $numServersUnavailable ++;
            }
        }

        if($numServersUnavailable == count($config["gearman"])) {
            $msg = "No Gearman server available";
            $this->logger->warning($msg);
            $this->brokerErrors[] = $msg;
        }

        $this->redis = new \Redis();
        $this->redis->connect($config["redis"]["host"], $config["redis"]["port"]);

        $this->timeOutForEndingJob = 30;
    }

    public function init() {
        $this->jobHandle = "";
        $this->terminateStatus = "";
        $this->brokerErrors = array();


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
        $this->startMicroTime = microtime(true);

        $status = false;

        $job_handle = $this->doBackground($function, $workload);

        if ($this->returnCode() == GEARMAN_SUCCESS)
        {
            $status = true;
            $this->jobHandle = $job_handle;

            $done = false;
            do
            {
                usleep(1000000);
                $duration = microtime(true) - $this->startMicroTime;
                $stat = $this->jobStatus($job_handle);
                if (!$stat[0]) // the job is unknown so it is done
                    $done = true;

                if($duration > $this->timeOutForEndingJob) {
                    $this->logger->info("Timeout thrown while waiting for end of job. Please check if workers are running");
                    break;
                }
            }
            while(!$done);
        }

        if($status) {
            return $this->terminate($job_handle);
        } else {
            $this->terminateStatus = "error with Gearman reception";
            return $this;
        }

    }

    private function terminate($job_handle) {
        $redisJobLog = $this->redis->get($job_handle);

        // if log hasn't been deleted by python worker, an exception occured
        if (false !== strpos($redisJobLog, "Wapistrano job ended")) {
            $this->logger->info("Job log: ". $this->redis->get($job_handle));
            $this->terminateStatus = "success";
        } elseif (false !== strpos($redisJobLog, "Wapistrano Job failed")) {
            $this->logger->error("Job error log: ". $redisJobLog);
            $this->terminateStatus = "error";
        }
        else {
            $this->logger->warning("Job error log: ". $redisJobLog);
            $this->terminateStatus = "running";
        }

        return $this;
    }

    public function delRedisLog($jobHandle) {
        $this->redis->del($jobHandle);
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

    /**
     * @return mixed
     */
    public function getBrokerErrors()
    {
        return $this->brokerErrors;
    }



}
