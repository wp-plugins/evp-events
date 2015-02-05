<?php
namespace Evp\Bundle\TicketBundle\EventDispatcher\Event\UserSession;

use Evp\Bundle\TicketBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class Destroyed
 * @package Evp\Bundle\TicketBundle\EventDispatcher\Event\UserSession
 */
class Destroyed extends Event {
    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    function __construct(User $user)
    {
        $this->user = $user;
    }
} 