<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\Projects;
use Wapistrano\CoreBundle\Form\ProjectsTypeAdd;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/")
 */
class HomeController extends Controller
{


    /**
     * @Route("/",  name="home")
     */
    public function indexAction()
    {

        $gmclient = $this->get("wapistrano_core.gearman");

        $status = $gmclient->doBackgroundAsync("publish_stage", "this is a test");

        if(!$status) {
            echo "pas glop";
        }

        $txt = "";

        return new Response($txt);
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


