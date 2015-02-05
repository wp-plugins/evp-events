<?php
/**
 * Check if discount is valid in TicketType scope
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\DiscountStrategy\DiscountScope;

use Evp\Bundle\TicketBundle\Entity\Discount;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;

/**
 * Class TicketTypeScope
 */
class TicketTypeScope extends ScopeAbstract implements ScopeInterface {

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Discount
     */
    protected $discount;

    /**
     * Validates Discount in TicketType scope
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Discount $discount
     * @return bool|int false or free amount of tickets
     */
    public function validateScope(Discount $discount) {
        $this->discount = $discount;
        $tickets = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->getAllByEventAndDiscountType($this->event, $discount->getDiscountType());

        $freeAmount = intval($discount->getDiscountType()->getTicketsCount()) - count($tickets);
        if ($freeAmount != 0) {
            return $freeAmount;
        }
        return false;
    }

    /**
     * Gets all Discounts for TicketType
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails $orderDetails
     * @return \Evp\Bundle\TicketBundle\Entity\DiscountType[]|void
     */
    public function findDiscountSiblings(OrderDetails $orderDetails) {
        $ticketType = $orderDetails->getTicketType();
        return $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\DiscountType')
            ->getAllActiveByTicketTypeAndScope($ticketType, $this->currentScope);
    }

    /**
     * Gets validated Discount entity
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Discount
     */
    public function getDiscount() {
        return $this->discount;
    }
} 
