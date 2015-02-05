<?php

namespace Evp\Bundle\TicketBundle\Listener\Step;

use Evp\Bundle\TicketBundle\Entity as Entities;
use Evp\Bundle\TicketBundle\EventDispatcher\Event\Step\Changed;
use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Service\OrderManager;
use Evp\Bundle\TicketBundle\Service\UserSession;
use Symfony\Bridge\Monolog\Logger;

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
     * @var UserSession
     */
    private $userSession;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param EntityManager $em
     * @param OrderManager $orderManager
     * @param UserSession $userSession
     * @param Logger $logger
     */
    function __construct(
        EntityManager $em,
        OrderManager $orderManager,
        UserSession $userSession,
        Logger $logger
    ) {
        $this->em = $em;
        $this->orderManager = $orderManager;
        $this->userSession = $userSession;
        $this->logger = $logger;
    }

    /**
     * When the first step has been validated
     *
     * @param Changed $event
     */
    public function onFirstStepCompleted(Changed $event) {
    }
}