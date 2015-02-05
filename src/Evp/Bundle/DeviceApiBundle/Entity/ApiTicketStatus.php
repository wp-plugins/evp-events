<?php

namespace Evp\Bundle\DeviceApiBundle\Entity;

use Evp\Bundle\TicketBundle\Entity\Ticket;
use JMS\Serializer\Annotation as JMS;

/**
 * Holds information about any actions performed on the ticket via API
 *
 * Class ApiTicketStatus
 * @package Evp\Bundle\DeviceApiBundle\Entity
 * @JMS\ExclusionPolicy("all")
 */
class ApiTicketStatus
{
    const EMPTY_FIELD = null;


    /**
     * @var string
     * @JMS\Expose
     */
    private $message;

    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @param Ticket $ticket
     */
    function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("ticket_status")
     *
     * @return string
     */
    public function getTicketStatus()
    {
        return $this->ticket
            ->getStatus()
            ;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("ticket_examiner")
     *
     * @return string
     */
    public function getTicketExaminer()
    {
        $ticketExaminer = $this->ticket->getTicketExaminer();

        if ($ticketExaminer === null) {
            return self::EMPTY_FIELD;
        }

        return $ticketExaminer->getName();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("ticket_used_date")
     *
     * @return string
     */
    public function getTicketUsageDate()
    {
        $dateUsed = $this->ticket->getDateUsed();

        if ($dateUsed === null) {
            return self::EMPTY_FIELD;
        }

        return $dateUsed;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param Ticket $ticket
     * @return self
     */
    public static function createFromTicket(Ticket $ticket)
    {
        return new static($ticket);
    }

} 