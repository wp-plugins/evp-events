<?php

namespace Evp\Bundle\PaymentBundle\PaymentHandler;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Evp\Bundle\TicketBundle\Entity as TicketEntities;
use Evp\Bundle\TicketBundle\Service\UserSession;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class HandlerAbstract
 * @package Evp\Bundle\PaymentBundle\PaymentHandler
 */
abstract class HandlerAbstract implements HandlerInterface
{
    /**
     * @var Router $router
     */
    private $router;

    /**
     * @var UserSession $userSession
     */
    protected $userSession;

    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * The order in which the handler is processed
     * (Later overrides Latter)
     *
     * @var int
     */
    private $rank;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /** @var string */
    private $name;

    /**
     * @param Router $router
     * @param UserSession $userSession
     * @param Logger $logger
     * @param EntityManager $entityManager
     */
    function __construct(
        Router $router,
        UserSession $userSession,
        Logger $logger,
        EntityManager $entityManager
    ) {
        $this->router = $router;
        $this->userSession = $userSession;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /**
     * @param TicketEntities\User $user
     * @return \Evp\Bundle\TicketBundle\Entity\Order
     */
    public function getOrderForUser($user)
    {
        return $user->getOrder();
    }

    /**
     * @param int $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }

    /**
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * {@inheritdoc}
     */
    public function handleCallback($request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getOkResponse()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorResponse()
    {
    }
}
