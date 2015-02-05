<?php
namespace Evp\Bundle\TicketMaintenanceBundle\Command;

use Evp\Bundle\TicketBundle\Entity\Order;
use Evp\Bundle\TicketBundle\Entity\Seat\Matrix;
use Evp\Bundle\TicketBundle\EventDispatcher\Event\Order\Expired;
use Evp\Bundle\TicketBundle\EventDispatcher\OrderEvents;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MarkExpiredOrdersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('orders:mark-expired')
            ->setDescription('If the order expiration date is past due, change the status to expired');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $dispatcher = $container->get('event_dispatcher');

        $expiredOrders = $entityManager->getRepository('EvpTicketBundle:Order')
            ->getAllWithExpirationDatePastDue();

        foreach ($expiredOrders as $order) {
            $order->setStatus(Order::STATUS_EXPIRED);
            $order->setDateFinished(new \DateTime('now'));

            $dispatcher->dispatch(OrderEvents::ON_EXPIRED, new Expired($order));
        }

        $entityManager->flush();
        $output->writeln(sprintf('Done, %d orders have been marked as expired', count($expiredOrders)));
    }
} 
