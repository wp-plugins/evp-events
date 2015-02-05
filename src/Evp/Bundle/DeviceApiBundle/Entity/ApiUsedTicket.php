<?php

namespace Evp\Bundle\DeviceApiBundle\Entity;

use Evp\Bundle\TicketBundle\Entity\Ticket;
use JMS\Serializer\Annotation as JMS;

/**
 * Holds information about a specific API ticket
 *
 * Class ApiUsedTicket
 * @package Evp\Bundle\DeviceApiBundle\Entity
 * @JMS\ExclusionPolicy("all")
 */
class ApiUsedTicket
{
    const EMPTY_FIELD = null;

    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @var string Ticket QR code contents
     */
    private $codeContents;

    /**
     * @param Ticket $ticket
     */
    function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * @param string $codeContents
     * @return ApiTicket
     */
    public function setCodeContents($codeContents)
    {
        $this->codeContents = $codeContents;

        return $this;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("ticket_code")
     *
     * @return string
     */
    public function getCodeContents()
    {
        if ($this->codeContents === null) {
            return self::EMPTY_FIELD;
        }

        return base64_encode($this->codeContents);
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("event_id")
     *
     * @return string
     */
    public function getEventId()
    {
        return $this->ticket
            ->getEvent()
            ->getId()
            ;
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
