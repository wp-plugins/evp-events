<?php

namespace Evp\Bundle\DeviceApiBundle\Entity;

use Evp\Bundle\TicketBundle\Entity\Ticket;
use JMS\Serializer\Annotation as JMS;

/**
 * Holds information about a specific API ticket
 *
 * Class ApiTicket
 * @package Evp\Bundle\DeviceApiBundle\Entity
 * @JMS\ExclusionPolicy("all")
 */
class ApiTicket
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
     * @JMS\VirtualProperty
     * @JMS\SerializedName("ticket_type")
     *
     * @return string
     */
    public function getTicketType()
    {
        return $this->ticket
            ->getTicketType()
            ->getName()
            ;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("discount_type")
     *
     * @return string
     */
    public function getDiscountType()
    {
        $discount = $this->ticket->getDiscount();
        if ($discount !== null) {
            return $discount->getDiscountType()->getName();
        }

        return self::EMPTY_FIELD;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("ticket_examiner")
     *
     * @return string
     */
    public function getTicketExaminer()
    {
        try {
            $ticketExaminer = $this->ticket->getTicketExaminer();

            if ($ticketExaminer === null) {
                return self::EMPTY_FIELD;
            }

            return $ticketExaminer->getName();
        } catch (\Exception $e) {
            return self::EMPTY_FIELD;
        }
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
     * @param Ticket $ticket
     * @return self
     */
    public static function createFromTicket(Ticket $ticket)
    {
        return new static($ticket);
    }

} 
