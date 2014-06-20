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
        $collector = $this->getContainer()->get("wapistrano_core.collector");
        $numUpdated = $collector->updateDeploymentsStatus();

        $output->writeln($numUpdated." deployments updated");
    }
}