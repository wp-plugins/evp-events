<?php
/**
 * Abstract class for DiscountScope
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Service\DiscountStrategy\DiscountScope;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Evp\Bundle\TicketBundle\Entity\User;
use Monolog\Logger;

/**
 * Class ScopeAbstract
 */
class ScopeAbstract {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Event
     */
    protected $event;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\TicketType
     */
    protected $ticketType;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\User
     */
    protected $user;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $currentScope;

    /**
     * Sets common requirements
     *
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Monolog\Logger $logger
     */
    public function __construct(EntityManager $entityManager, Logger $logger) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Sets Event
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $event
     */
    public function setEvent(Event $event) {
        $this->event = $event;
    }

    /**
     * Sets Ticket
     *
     * @param \Evp\Bundle\TicketBundle\Entity\TicketType $ticketType
     */
    public function setTicketType(TicketType $ticketType) {
        $this->ticketType = $ticketType;
    }

    /**
     * Sets User
     *
     * @param \Evp\Bundle\TicketBundle\Entity\User $user
     */
    public function setUser(User $user) {
        $this->user = $user;
    }

    /**
     * Sets current Scope
     * @param string $scope
     */
    public function setCurrentScope($scope) {
        $this->currentScope = $scope;
    }
}
