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
        $brokerService = $this->container->get('wapistrano_core.gearman');
        $twigVars = array();
        $twigVars['barTitle'] = "Wapistrano Status";
        $twigVars["brokerIsUp"] = true;
        $twigVars["workersAreUp"] = true;

        if(count($brokerService->getBrokerErrors()) == 0) {
            $admin = new Gadm();

            $twigVars["brokerVersion"] = 'gearman version: ' . $admin->getVersion() . "\n";
            $twigVars["brokerStatus"] = $admin->getStatus();
            if(count($admin->getWorkersAsArray()) == 0) {
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


    /**
     * A worker, just for example
     */
    public function workerTest() {
        // Reverse Worker Code
        $worker = new \GearmanWorker();
        $worker->addServer();
        $worker->addFunction("reverse", function ($job) {
            return "Yo man !!! See that : ".strrev($job->workload());
        });
        while ($worker->work());
    }

    public function clientTest() {
        flush();

        # Create our client object.
        $client= new \GearmanClient();
        # Add default server (app.gearmanhq.com).
        $client->addServer();
        echo "Sending job...\n";
        flush();
        # Send reverse job
        $result = $client->doNormal("reverse", "Hello World!");
        echo "Result: {$result}" . PHP_EOL;

        $txt = "";

        return new Response($txt);
    }
    public function test()
    {
        ini_set("implicit_flush", 1);
        echo ini_get("implicit_flush");
        # Create our client object.
        $gmclient= new \GearmanClient();
        $gmclient->addServer();
        do
        {
            $result = $gmclient->doNormal("reverse", "Hello!");
            # Vérifie les paquets et les erreurs retournés.

            switch($gmclient->returnCode())
            {
                case GEARMAN_WORK_DATA:
                    echo "Donn+++ : $result\n";
                    break;
                case GEARMAN_WORK_STATUS:
                    list($numerator, $denominator)= $gmclient->doStatus();
                    echo "Statut : $numerator/$denominator complete\n";
                    break;
                case GEARMAN_WORK_FAIL:
                    echo "Échec\n";
                    exit;
                case GEARMAN_SUCCESS:
                    echo "Données : $result\n";
                    break;
                default:
                    echo "RET : " . $gmclient->returnCode() . "\n";
                    echo "Erreur : " . $gmclient->error() . "\n";
                    echo "N° de l'erreur : " . $gmclient->getErrno() . "\n";
                    exit;
            }
        }
        while($gmclient->returnCode() != GEARMAN_SUCCESS);



        $txt = "";

        return new Response($txt);
    }

    public function testRedis() {
        $redis = new \Redis();
        $redis->connect("127.0.0.1", 6379);
    }



}


