<?php
/**
 * Abstract class for Manager services
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;

/**
 * Class ManagerAbstract
 */
class ManagerAbstract {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Sets common Services for Managers
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
}
