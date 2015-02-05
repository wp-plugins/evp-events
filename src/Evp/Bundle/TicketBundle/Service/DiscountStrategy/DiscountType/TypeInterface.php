<?php
/**
 * General discount type interface
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Service\DiscountStrategy\DiscountType;

/**
 * Interface TypeInterface
 */
interface TypeInterface {

    /**
     * Applies associated TicketTypeDiscount by it's Type
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Ticket[] $tickets
     * @return bool
     */
    public function apply($tickets);

    /**
     * @param integer $amount
     *
     * @return object
     */
    public function setAmount($amount);
}
