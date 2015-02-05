<?php

namespace Evp\Bundle\TicketBundle\Listener\UserSession;


use Evp\Bundle\TicketBundle\EventDispatcher\Event\UserSession\Created;
use Evp\Bundle\TicketBundle\EventDispatcher\Event\UserSession\Destroyed;
use Evp\Bundle\TicketBundle\Service\OrderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Evp\Bundle\TicketBundle\Entity\Order as OrderEntity;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class Order
 * @package Evp\Bundle\TicketBundle\Listener\UserSession
 */
class Step
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var string[]
     */
    private $keysToBeRemovedList;

    /**
     * @param Session $session
     * @param string[] $keysToBeRemovedList
     */
    function __construct(Session $session, $keysToBeRemovedList)
    {
        $this->keysToBeRemovedList = $keysToBeRemovedList;
        $this->session = $session;
    }

    /**
     * @param Destroyed $event
     *
     * Remove Step session keys when the user is destroyed
     */
    public function onSessionDestroy(Destroyed $event)
    {
        foreach ($this->keysToBeRemovedList as $key) {
            $this->session->remove($key);
        }
    }
}