<?php
/**
 * Helper entity for Discounts listing control
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Entity\Step;

/**
 * Class DiscountDetails
 */
class DiscountDetails {

    /**
     * @var array
     */
    private $discountType;

    /**
     * @var string
     */
    private $discountValue;

    /**
     * @var int
     */
    private $orderDetailsId;

    /**
     * @var int
     */
    private $ticketType;

    /**
     * @var int
     */
    private $ticket;

    /**
     * @param int $ticket
     * @return self
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * @return int
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param int $ticketType
     * @return self
     */
    public function setTicketType($ticketType)
    {
        $this->ticketType = $ticketType;

        return $this;
    }

    /**
     * @return int
     */
    public function getTicketType()
    {
        return $this->ticketType;
    }

    /**
     * @param mixed $orderDetailsId
     * @return self
     */
    public function setOrderDetailsId($orderDetailsId)
    {
        $this->orderDetailsId = $orderDetailsId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderDetailsId()
    {
        return $this->orderDetailsId;
    }

    /**
     * @param mixed $discountValue
     * @return self
     */
    public function setDiscountValue($discountValue)
    {
        $this->discountValue = $discountValue;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscountValue()
    {
        return $this->discountValue;
    }

    /**
     * @param mixed $discountType
     * @return self
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }
}
