<?php
namespace Evp\Bundle\TicketBundle\EventDispatcher\Event\UserSession;

use Evp\Bundle\TicketBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class Created
 * @package Evp\Bundle\TicketBundle\EventDispatcher\Event\UserSession
 */
class Created extends Event {
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

    /**
     * @param \Evp\Bundle\TicketBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \Evp\Bundle\TicketBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }


} 