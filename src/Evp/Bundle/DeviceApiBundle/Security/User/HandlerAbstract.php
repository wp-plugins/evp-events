<?php
/**
 * Common class for authentication handlers
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\DeviceApiBundle\Security\User;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;

/**
 * Class HandlerAbstract
 * @package Evp\Bundle\DeviceApiBundle\Security\Authentication\Provider
 */
abstract class HandlerAbstract {

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $em
     * @param Logger $log
     */
    public function __construct(
        EntityManager $em,
        Logger $log
    ) {
        $this->entityManager = $em;
        $this->logger = $log;
    }
}
