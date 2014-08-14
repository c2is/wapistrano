<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CoreBundle\Form\ProjectsTypeAdd;
use Symfony\Component\HttpFoundation\Request;
use Wapistrano\CoreBundle\Broker\WapistranoGearmanAdmin as Gadm;

/**
 * @Route("/")
 */
class HomeController extends Controller
{


    /**
     * @Route("/",  name="home")
     * @Template("WapistranoCoreBundle::home.html.twig")
     */
    public function indexAction()
    {
        $collector = $this->get("wapistrano_core.collector");
        $collector->updateDeploymentsStatus();

        $brokerService = $this->container->get('wapistrano_core.gearman');
        $twigVars = array();
        $twigVars['barTitle'] = "Wapistrano Status";
        $twigVars["brokerIsUp"] = true;
        $twigVars["workersAreUp"] = true;

        if (count($brokerService->getBrokerErrors()) == 0) {
            $admin = new Gadm();

            $twigVars["brokerVersion"] = 'gearman version: ' . $admin->getVersion() . "\n";
            $twigVars["brokerStatus"] = $admin->getStatus();
            if (count($admin->getWorkersAsArray()) == 0) {
                $twigVars["workersAreUp"] = false;
                $twigVars["message"] = "No worker running, no task will be handled now";
            } else {
                $workersOpt = "";
                foreach ($admin->getWorkersAsArray() as $worker) {
                    $workersOpt .= sprintf("%16d | %15s | %-10s | %s\n", $worker->getFd(), $worker->getIp(), $worker->getClientId(), implode(' ', $worker->getFunctions()));
                }
                $twigVars["brokerWorkers"] = $workersOpt;
            }


        } else {
            $twigVars["brokerIsUp"] = false;

            if (!$sock = @fsockopen('www.google.fr', 80, $num, $error, 5)) {
                $twigVars["message"] = "Internet connexion seems to be down";
            } else {
                $twigVars["message"] = "Gearman broker is not available";
            }

        }

        $em = $this->container->get('doctrine')->getManager();
        $deploymentsRepo = $em->getRepository('WapistranoCoreBundle:Deployments');

        $deploymentsRunning = $deploymentsRepo->findBy(array("status" => "running"), array("createdAt" => "DESC"));
        $twigVars["deploymentsRunning"] = $deploymentsRunning;

        $deploymentsFailed = $deploymentsRepo->findBy(array("status" => "failed"), array("createdAt" => "DESC"), 5);
        $twigVars["deploymentsFailed"] = $deploymentsFailed;

        $deploymentsSuccess = $deploymentsRepo->findBy(array("status" => "success"), array("createdAt" => "DESC"), 5);
        $twigVars["deploymentsSuccess"] = $deploymentsSuccess;

        return $twigVars;
    }


}


