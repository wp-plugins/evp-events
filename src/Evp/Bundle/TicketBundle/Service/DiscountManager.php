<?php
/**
 * DiscountManager service
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\DiscountType;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Evp\Bundle\TicketBundle\Entity\User;
use Monolog\Logger;

/**
 * Class DiscountManager
 */
class DiscountManager extends ManagerAbstract {

    /**
     * @var \Evp\Bundle\TicketBundle\Service\DiscountStrategy\StrategyInterface[]
     */
    private $discountStrategies;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\DiscountStrategy\DiscountScope\ScopeInterface[]
     */
    private $scopes;

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
     * @var \Evp\Bundle\TicketBundle\Entity\Discount
     */
    protected $discount;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\DiscountStrategy\DiscountType\TypeInterface[]
     */
    protected $types;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Monolog\Logger $logger
     * @param array $strategies
     * @param array $types
     * @param array $scopes
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        $strategies,
        $types,
        $scopes
    ) {
        parent::__construct($entityManager, $logger);
        $this->discountStrategies = $strategies;
        $this->types = $types;
        $this->scopes = $scopes;
    }

    /**
     * Validates code against discount strategy and sets matched Discount
     *
     * @param string $code
     * @param \Evp\Bundle\TicketBundle\Entity\DiscountType $discountType
     * @return int
     */
    public function validateCode($code, DiscountType $discountType) {
        if (!$this->checkDiscountDatesAndStatus($discountType)) {
            return false;
        }
        $this->setStrategyDependencies($discountType->getDiscountStrategy());
        $this->setStrategyScopes($discountType->getDiscountStrategy());

        $strategyResult = $this->discountStrategies[$discountType->getDiscountStrategy()]
            ->validate($code, $discountType);
        $this->discount = $this->discountStrategies[$discountType->getDiscountStrategy()]->getDiscount();

        return $strategyResult;
    }

    /**
     * Validates generic DiscountType settings before proceeding
     *
     * @param \Evp\Bundle\TicketBundle\Entity\DiscountType $discountType
     * @return bool
     */
    public function checkDiscountDatesAndStatus(DiscountType $discountType) {
        if (
            $discountType->getStatus()
            && $discountType->getDateStarts() <= new \DateTime
            && $discountType->getDateEnds() >= new \DateTime
        ) {
            return true;
        }
        return false;
    }

    /**
     * Applies already validated and persisted TicketTypeDiscount to given Ticket[]
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Ticket[] $tickets
     * @return bool
     */
    public function applyDiscount($tickets) {
        $this->types[$this->discount->getDiscountType()->getType()]->setAmount($this->discount->getValue());
        return $this->types[$this->discount->getDiscountType()->getType()]->apply($tickets);
    }

    /**
     * @param OrderDetails $orderDetails
     * @return null
     */
    public function getDiscountChoicesByOrderDetails(OrderDetails $orderDetails) {
        $choices = null;
        $availableDiscounts = array();
        foreach ($this->scopes as $name => $scope) {
            $scope->setCurrentScope($name);
            $availableDiscounts = array_merge($scope->findDiscountSiblings($orderDetails), $availableDiscounts);
        }

        foreach ($availableDiscounts as $discountType) {
            $choices[$discountType->getId()] = $discountType->getName();
        }
        return $choices;
    }

    /**
     * @param Event $event
     * @param User $user
     * @param TicketType $ticketType
     */
    public function setDependentEntities(Event $event, User $user, TicketType $ticketType) {
        $this->event = $event;
        $this->user = $user;
        $this->ticketType = $ticketType;
    }

    /**
     * @param $strategy
     */
    private function setStrategyDependencies($strategy) {
        $this->discountStrategies[$strategy]->setEvent($this->event);
        $this->discountStrategies[$strategy]->setUser($this->user);
        $this->discountStrategies[$strategy]->setTicketType($this->ticketType);
    }

    /**
     * @param string $strategy
     */
    private function setStrategyScopes($strategy) {
        $this->discountStrategies[$strategy]->setScopes($this->scopes);
    }

    /**
     * Returns Discount Entity, if validation was successful
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Discount
     */
    public function getValidatedDiscount() {
        return $this->discount;
    }

    /**
     * @return array
     */
    public function getAvailableStrategies() {
        $arr = array();
        foreach (array_keys($this->discountStrategies) as $key) {
            $arr[$key] = $key;
        }
        return $arr;
    }

    /**
     * @return array
     */
    public function getAvailableTypes() {
        $arr = array();
        foreach (array_keys($this->types) as $key) {
            $arr[$key] = $key;
        }
        return $arr;    }

    /**
     * @return array
     */
    public function getAvailableScopes() {
        $arr = array();
        foreach (array_keys($this->scopes) as $key) {
            $arr[$key] = $key;
        }
        return $arr;    }
}
