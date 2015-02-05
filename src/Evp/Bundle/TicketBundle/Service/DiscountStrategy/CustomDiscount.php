<?php
/**
 * Custom-admin created discount
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\DiscountStrategy;

use Evp\Bundle\TicketBundle\Entity\DiscountType;

/**
 * Class CustomDiscount
 */
class CustomDiscount extends StrategyAbstract implements StrategyInterface {

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Discount
     */
    protected $discount;

    /**
     * Validate discount code
     *
     * @param string $code
     * @param \Evp\Bundle\TicketBundle\Entity\DiscountType $discountType
     * @return \Evp\Bundle\TicketBundle\Entity\Discount
     */
    public function validate($code, DiscountType $discountType) {
        $discounts = $discountType->getDiscounts();
        $found = null;
        foreach ($discounts as $discount) {
            if ($discount->getToken() == $code) {
                $this->logger->debug('found discount code', array('code' => $code, 'discount' => $discount->getId()));
                $found = $discount;
                break;
            }
        }
        if (!$found) {
            return false;
        }
        if(!$discountType->getTicketsCount()) {
            return true;
        }
        $this->setScopeDependencies($discountType->getScope());
        $freeTickets = $this->discountScopes[$discountType->getScope()]->validateScope($found);
        $this->discount = $this->discountScopes[$discountType->getScope()]->getDiscount();

        return $freeTickets;
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