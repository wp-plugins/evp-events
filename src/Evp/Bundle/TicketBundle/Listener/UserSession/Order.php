<?php

namespace Evp\Bundle\TicketBundle\Listener\UserSession;


use Evp\Bundle\TicketBundle\EventDispatcher\Event\UserSession\Created;
use Evp\Bundle\TicketBundle\EventDispatcher\Event\UserSession\Destroyed;
use Evp\Bundle\TicketBundle\Service\OrderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Evp\Bundle\TicketBundle\Entity\Order as OrderEntity;
use Doctrine\ORM\EntityManager;


/**
 * Class Order
 * @package Evp\Bundle\TicketBundle\Listener\UserSession
 */
class Order
{
    /**
     * @var EntityManager
     */
    private $em;


    /**
     * @var OrderManager
     */
    private $orderManager;


    /**
     * @param EntityManager $em
     * @param OrderManager $orderManager
     */
    function __construct(
        EntityManager $em,
        OrderManager $orderManager
    ) {
        $this->em = $em;
        $this->orderManager = $orderManager;
    }


    /**
     * @param Created $event
     */
    public function onSessionCreate(Created $event)
    {
        $order = $this->orderManager->createFromUser($event->getUser());
        $event->getUser()->setOrder($order);
        $this->em->flush();
    }

    /**
     * @param Destroyed $event
     */
    public function onSessionDestroy(Destroyed $event)
    {
    }
}