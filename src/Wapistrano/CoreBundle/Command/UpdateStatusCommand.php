<?php
namespace Wapistrano\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class UpdateStatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
        ->setName('wapistrano:update-status')
        ->setDescription('Check deployment status in redis and update database according to that');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gmClient = $this->getContainer()->get("wapistrano_core.gearman");
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repoDeployments  = $em->getRepository('WapistranoCoreBundle:Deployments');

        $runningDeployments = $repoDeployments->findBy(array("status" => "running"));

        foreach ($runningDeployments as $deploy) {
            $jobLog = $gmClient->getLog($deploy->getJobHandle());

            if (false !== strpos($jobLog, "Wapistrano job ended")) {
                $output->writeln("deploy id ".$deploy->getId()." ended, redis jobHandle: ".$deploy->getJobHandle()." now deleting log...");
                $gmClient->delRedisLog($deploy->getJobHandle());
                $deploy->setStatus("success");
                $em->persist($deploy);
            } elseif (false !== strpos($jobLog, "Wapistrano Job failed")) {
                $output->writeln("deploy id ".$deploy->getId()." failed, redis jobHandle: ".$deploy->getJobHandle()." now deleting log...");
                $gmClient->delRedisLog($deploy->getJobHandle());
                $deploy->setStatus("failed");
                $em->persist($deploy);
            }
            $em->flush();
        }
    }
}