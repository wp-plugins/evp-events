<?php
/**
 * Interface for various Ticket code generator strategies
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Service\TicketCodeStrategy;

use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Ticket;

/**
 * Interface TicketCodeStrategyInterface
 */
interface TicketCodeStrategyInterface
{
    const STRATEGY_QR_CODE = 'qr_code';
    const STRATEGY_BAR_CODE = 'bar_code';

    /**
     * Creates Code image for given Ticket
     *
     * @param Ticket $ticket
     *
     * @return string
     */
    function createFromTicket(Ticket $ticket);

    /**
     * Creates Code image for given Event
     *
     * @param Event $event
     *
     * @return string
     */
    function createFromEvent(Event $event);

    /**
     * Creates Code image for given string
     *
     * @param string $string
     *
     * @return string
     */
    function createFromString($string);
} 
