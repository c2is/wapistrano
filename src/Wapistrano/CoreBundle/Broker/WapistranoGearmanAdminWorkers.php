<?php
namespace Wapistrano\CoreBundle\Broker;

use Wapistrano\CoreBundle\Broker\Libs\PhpGearmanAdmin\GearmanAdminWorkers;

class WapistranoGearmanAdminWorkers extends GearmanAdminWorkers
{

	public function __construct(array $workers) {
		parent::__construct($workers);
    }


}
