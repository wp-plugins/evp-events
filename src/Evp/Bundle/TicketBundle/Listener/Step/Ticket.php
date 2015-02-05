<?php

namespace Evp\Bundle\TicketBundle\Listener\Step;

use Evp\Bundle\TicketBundle\Entity\Order as EntityOrder;
use Evp\Bundle\TicketBundle\EventDispatcher\Event\Step\Changed;
use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Service\OrderManager;
use Evp\Bundle\TicketBundle\Service\UserSession;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class Ticket
 * @package Evp\Bundle\TicketBundle\Listener\UserSession
 */
class Ticket
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

    /**
     * When the last step has been validated
     *
     * @param Changed $event
     * @throws \Exception
     */
    public function onLastStepCompleted(Changed $event) {
        $order = $event->getUser()->getOrder();

        if ($order->getStatus() === EntityOrder::STATUS_IN_PROGRESS) {
            $this->orderManager->extendLongTermReservationTime($order);
            $order->setStatus(EntityOrder::STATUS_AWAITING_PAYMENT);
            $this->em->flush();
        }
    }

    /**
     * When the user clicks on the next button
     *
     * @param Changed $event
     */
    public function onNextStep(Changed $event) {
        $order =  $event->getUser()->getOrder();
        $this->destroySessionIfOrderExpired($order);
        $this->orderManager->extendShortTermReservationTime($order);
    }

    /**
     * When the user clicks on the previous button
     *
     * @param Changed $event
     */
    public function onPreviousStep(Changed $event) {
        $order =  $event->getUser()->getOrder();
        $this->destroySessionIfOrderExpired($order);
    }

    /**
     * When the user clicks cancel
     *
     * @param Changed $event
     */
    public function onCancel(Changed $event) {
    }

    /**
     * @param EntityOrder $order
     */
    private function destroySessionIfOrderExpired($order)
    {
        if ($this->orderManager->hasOrderExpired($order)) {
            $this->logger->warning(
                'User accessed step with an expired order - removing user session',
                array('order' => $order)
            );
            $this->userSession->destroyCurrentSession();
        }
    }
}