<?php
/**
 * General discount scope interface
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Service\DiscountStrategy\DiscountScope;

use Evp\Bundle\TicketBundle\Entity\Discount;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;

/**
 * Interface ScopeInterface
 */
interface ScopeInterface {

    /**
     * Validates Discount in given scope
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Discount $discount
     * @return bool|int
     */
    function validateScope(Discount $discount);

    /**
     * Gets all DiscountType[] by it's scope
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails $orderDetails
     * @return \Evp\Bundle\TicketBundle\Entity\DiscountType[]
     */
    function findDiscountSiblings(OrderDetails $orderDetails);

    /**
     * Gets validated Discount entity
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Discount
     */
    function getDiscount();
}
