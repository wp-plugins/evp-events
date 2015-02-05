<?php
namespace Evp\Bundle\TicketBundle\Listener\UserSession;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\EventDispatcher\Event\UserSession\Created;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class Event
 * @package Evp\Bundle\TicketBundle\Listener\UserSession
 *
 *
 */
class Event
{
    /**
     * @var
     */
    private $eventIdSessionKey;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var         EntityManager $em,
     */
    private $em;

    /**
     * @param Session $session
     * @param $eventIdSessionKey
     * @param Logger $logger
     * @param EntityManager $em,
     */
    function __construct(
        Session $session,
        $eventIdSessionKey,
        Logger $logger,
        EntityManager $em
    )
    {
        $this->session = $session;
        $this->eventIdSessionKey = $eventIdSessionKey;
        $this->logger = $logger;
        $this->em = $em;
    }

    /**
     * Attaches event information to the user order once a new session is created
     *
     * @param Created $event
     * @throws \Exception
     */
    public function onSessionCreate(Created $event)
    {
        $this->logger->debug(__METHOD__);
        $this->logger->debug('Attaching event information to the user');

        $eventSavedInSession = $this->session->has($this->eventIdSessionKey);

        if (!$eventSavedInSession) {
            $this->logger->error('No event was found in the session',
                array(
                    'event' => $event
                )
            );

            throw new \Exception('No event information found in session');
        }

        $ticketEventId = $this->session->get($this->eventIdSessionKey);
        $ticketEvent = $this->em->getRepository('EvpTicketBundle:Event')
            ->find($ticketEventId);

        $event->getUser()
            ->getOrder()
            ->setEvent($ticketEvent);

        $this->em->flush();
    }
} 