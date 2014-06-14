<?php

namespace Wapistrano\CoreBundle\Broker;

use Wapistrano\CoreBundle\Broker\Libs\PhpGearmanAdmin\GearmanAdmin;
use Wapistrano\CoreBundle\Broker\WapistranoGearmanAdminWorkers;
class WapistranoGearmanAdmin extends GearmanAdmin
{
    private $host;
    private $port;
    private $timeout;
    private $workers;
    public function __construct($host = '127.0.0.1', $port = 4730, $timeout = 500) {
        parent::__construct($host, $port, $timeout);
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }
    public function getWorkersAsArray($connection = null) {
        if ($connection === null) {
            $gearman = $this->connect();
        } else {
            $gearman = $connection;
        }

        fputs($gearman, "workers\n");
        $rawWorkers = array();
        while (!feof($gearman) && ($line = fgets($gearman)) && ($line != ".\n")) {
            $rawWorkers[] = $line;
        }
        $this->workers = new WapistranoGearmanAdminWorkers($rawWorkers);

        if ($connection === null) {
            fclose($gearman);
        }

        return $this->workers->getWorkers();
    }

    private function connect() {
        $errno  = 0;
        $errstr = '';
        $resource = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout / 1000);

        if (!$resource) {
            throw new RuntimeException("Failed to connect to gearman server at {$this->host}:{$this->port}. Error number [$errno], message: $errstr");
        } else {
            stream_set_timeout($resource, 0, $this->timeout * 1000);
            return $resource;
        }
    }
}
