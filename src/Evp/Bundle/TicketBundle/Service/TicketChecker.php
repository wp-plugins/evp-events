<?php
namespace Evp\Bundle\TicketBundle\Service;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketChecker
 * @package Evp\Bundle\TicketBundle\Service
 */
class TicketChecker
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    /**
     * @var \Monolog\Logger
     */
    private $logger;
    /**
     * @var
     */
    private $ticketCheckerKey;
    /**
     * @var
     */
    private $cookieValidityTime;

    /**
     * @param EntityManager $entityManager
     * @param Logger $logger
     * @param $ticketCheckerKey
     * @param $cookieValidityTime
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        $ticketCheckerKey,
        $cookieValidityTime
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->ticketCheckerKey = $ticketCheckerKey;
        $this->cookieValidityTime = $cookieValidityTime;
    }


    /**
     * Checks if the user who sent the request is authorized to check the ticket
     *
     * @param Ticket $ticket
     * @param Request $request
     * @return bool
     */
    public function isAuthorizedToCheckTicket(Ticket $ticket, Request $request) {
        $this->logger->debug(__METHOD__, array(func_get_args()));
        $requestedEventToken = $ticket->getEvent()->getToken();

        if ($request->cookies->has($this->ticketCheckerKey)) {
            $serializedTokens = stripslashes($request->cookies->get($this->ticketCheckerKey));
            $allowedEventTokens = json_decode($serializedTokens);

            if (in_array($requestedEventToken, $allowedEventTokens)) {
                return true;
            }
        }

        return false;
    }
}