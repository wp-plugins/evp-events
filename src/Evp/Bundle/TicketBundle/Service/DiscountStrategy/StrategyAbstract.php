<?php
/**
 * Abstract Class for Discount strategies
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\DiscountStrategy;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Evp\Bundle\TicketBundle\Entity\User;
use Monolog\Logger;

/**
 * Class StrategyAbstract
 */
abstract class StrategyAbstract {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\DiscountStrategy\DiscountScope\ScopeInterface[]
     */
    protected $discountScopes;

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
     * @var int
     */
    protected $scopeResult;

    /**
     * Sets requirements
     *
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Monolog\Logger $logger
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Sets dependencies for any Scope
     *
     * @param string $string scope Id
     */
    public function setScopeDependencies($string) {
        $this->discountScopes[$string]->setUser($this->user);
        $this->discountScopes[$string]->setEvent($this->event);
        $this->discountScopes[$string]->setTicketType($this->ticketType);
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
     * @param array $scopes
     * @return self
     */
    public function setScopes($scopes) {
        $this->discountScopes = $scopes;
    }

    /**
     * @return array
     */
    public function getAvailableScopes() {
        return array_keys($this->discountScopes);
    }
}
