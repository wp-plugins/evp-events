<?php
/**
 * General discount strategy interface
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Service\DiscountStrategy;

use Evp\Bundle\TicketBundle\Entity\DiscountType;

/**
 * Class StrategyInterface
 */
interface StrategyInterface {

    /**
     * Validate given discount code
     * @param string $code
     * @param \Evp\Bundle\TicketBundle\Entity\DiscountType $discountType
     * @return bool
     */
    function validate($code, DiscountType $discountType);

    /**
     * Gets validated Discount entity
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Discount
     */
    function getDiscount();
}
