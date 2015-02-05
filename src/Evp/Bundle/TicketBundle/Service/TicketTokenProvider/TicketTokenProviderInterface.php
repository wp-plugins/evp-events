<?php
/**
 * Interface for various TicketToken strategies
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\TicketTokenProvider;

use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Ticket;

/**
 * Interface TicketTokenProviderInterface
 */
interface TicketTokenProviderInterface
{
    /**
     * Sets current service priority
     *
     * @param string $priority
     */
    function setPriority($priority);

    /**
     * Gets the priority for current Service
     *
     * @return int
     */
    function getPriority();

    /**
     * Gets the Token for given Event, if one is available
     *
     * @param Ticket $ticket
     * @param Event  $event
     *
     * @return string|null
     */
    function generateTokenForTicketByEvent(Ticket $ticket, Event $event);

    /**
     * Frees-up given token and Ticket. After this token can be reused again
     *
     * @param string $token
     * @param Ticket $ticket
     *
     * @return bool
     */
    function unbindTokenByTicket($token, Ticket $ticket);
}
