<?php
/**
 * Manages the Ticket tokens by specified Provider
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketBundle\Exception\NoTokenFoundException;
use Evp\Bundle\TicketBundle\Service\TicketTokenProvider\TicketTokenProviderInterface;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class TicketTokenManager
 */
class TicketTokenManager
{
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var TicketTokenProviderInterface[]
     */
    private $generators;

    /**
     * @param Logger $log
     */
    public function __construct(Logger $log)
    {
        $this->logger = $log;
    }

    /**
     * @param TicketTokenProviderInterface $service
     * @param string                       $name
     */
    public function addGenerator(TicketTokenProviderInterface $service, $name)
    {
        $this->generators[$name] = $service;
    }

    /**
     * Sorts all Generators by their priorities
     */
    public function sortGenerators()
    {
        uasort(
            $this->generators,
            function (TicketTokenProviderInterface $a, TicketTokenProviderInterface $b) {
                return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
            }
        );
    }

    /**
     * Gets the Token by particular event from first available Generator
     *
     * @param Ticket $ticket
     * @param Event  $event
     *
     * @throws \Evp\Bundle\TicketBundle\Exception\NoTokenFoundException
     * @return string
     */
    public function getTokenForTicketByEvent(Ticket $ticket, Event $event)
    {
        $this->logger->debug(
            'Trying to get Token for Event with Generators',
            array(
                'event' => $event->getId(),
                'generators' => array_keys($this->generators),
            )
        );
        $token = null;
        foreach ($this->generators as $name => $generator) {
            $this->logger->debug('Checking Generator Service for new Token', array($name));
            $token = $generator->generateTokenForTicketByEvent($ticket, $event);
            if (!empty($token)) {
                $this->logger->debug('Got new Token by Generator Service', array($token, $name));
                return $token;
            }
        }

        throw new NoTokenFoundException('Failed to find Token for Event ' .$event->getId());
    }

    /**
     * Frees up Token string in Ticket context
     *
     * @param Ticket $ticket
     */
    public function unbindTokenByTicket(Ticket $ticket)
    {
        $token = $ticket->getToken();
        $this->logger->debug('Trying to unbind token', array($token));
        foreach ($this->generators as $name => $generator) {
            $this->logger->debug('Checking if Generator Service can unbind token', array($name));
            try {
                $generator->unbindTokenByTicket($token, $ticket);
            } catch (\Exception $e) {
                $this->logger->debug('Got Exception unbinding Token', array($e));
            }
        }
    }
}
