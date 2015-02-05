<?php
/**
 * Abstract class for DiscountType
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Service\DiscountStrategy\DiscountType;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;

/**
 * Class TypeAbstract
 */
class TypeAbstract {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

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
     * Sets discount amount for DiscountType
     * @param $amount
     */
    public function setAmount($amount) {
        $this->amount = $amount;
    }
}
