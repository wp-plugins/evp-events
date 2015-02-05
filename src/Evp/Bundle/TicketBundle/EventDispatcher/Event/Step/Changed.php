<?php
namespace Evp\Bundle\TicketBundle\EventDispatcher\Event\Step;

use Evp\Bundle\TicketBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;


class Changed extends Event {
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
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }


} 