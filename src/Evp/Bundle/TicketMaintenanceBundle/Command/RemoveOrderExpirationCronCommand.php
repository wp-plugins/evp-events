<?php
namespace Evp\Bundle\TicketMaintenanceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveOrderExpirationCronCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this->setName('orders:cron:remove')
            ->setDescription('Removes the cron for order expiration');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $job = $container->get('evp_ticket_maintenance.cron_tab.job');

        $container->get('evp_ticket_maintenance.cron_tab')
            ->removeJob($job)
            ->write();
    }
} 