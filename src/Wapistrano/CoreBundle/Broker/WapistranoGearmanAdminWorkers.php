<?php
namespace Wapistrano\CoreBundle\Broker;

use Wapistrano\CoreBundle\Broker\Libs\PhpGearmanAdmin\GearmanAdminWorkers;
use Wapistrano\CoreBundle\Broker\Libs\PhpGearmanAdmin\GearmanAdminWorker;

class WapistranoGearmanAdminWorkers extends GearmanAdminWorkers
{

    /** @var GearmanAdminWorker[] The contained workers. */
    private $_workers = array();

    /**
     * Construct a new GearmanAdminWorkers, parsing the given worker strings.
     *
     * @param string[] $workers
     */
    public function __construct(array $workers) {
        $this->_parseWorkers($workers);
    }

    /**
     * Get the workers as a pretty string.
     *
     * @see http://www.php.net/manual/en/language.oop5.magic.php#object.tostring
     *
     * @return string
     */
    public function __toString() {
        $res  = "File descriptor: | IP address:     | Client id: | Functions:\n";
        $res .= "-------------------------------------------------------------\n";

        foreach ($this->getWorkers() as $worker) {
            $res .= sprintf("%16d | %15s | %-10s | %s\n", $worker->getFd(), $worker->getIp(), $worker->getClientId(), implode(' ', $worker->getFunctions()));
        }

        return $res;
    }

    /**
     * Get all the registered workers.
     *
     * @return GearmanAdminWorker[]
     */
    public function getWorkers() {
        return $this->_workers;
    }

    /**
     * Parse a workers string array as returned by the gearman server.
     *
     * @param string[] $workers
     *
     * @return null
     */
    private function _parseWorkers(array $workers) {
        foreach ($workers as $line) {
            $matches = array();
            if (preg_match('/^([0-9]{2}) ([0-9.]*) (python_worker .*) : (.*)$/', $line, $matches)) {
                $this->_workers[] = new GearmanAdminWorker((integer) $matches[1], $matches[2], $matches[3], explode(' ', $matches[4]));
            }
        }
    }


}
