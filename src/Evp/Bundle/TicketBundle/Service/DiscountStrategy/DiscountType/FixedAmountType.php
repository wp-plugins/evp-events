<?php
/**
 * Apply a fixed amount discount to Ticket price
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\DiscountStrategy\DiscountType;

/**
 * Class FixedAmountType
 */
class FixedAmountType extends TypeAbstract implements TypeInterface {

    /**
     * Applies a fixed Discount amount from Discount
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Ticket[] $tickets
     * @return bool|void
     */
    public function apply($tickets) {
        foreach ($tickets as $ticket) {
            $newPrice = floatval($ticket->getPrice()) - floatval($this->amount);
            if ($newPrice <= 0) {
                $newPrice = 0.01;
            }
            $ticket->setPrice(round($newPrice, 2, PHP_ROUND_HALF_UP));
            $ticket->setDateModified(new \DateTime);
            $this->logger->debug('applied discount data', array('discount' => $this->amount, 'ticket' => $ticket));
            $this->entityManager->persist($ticket);
        }
        $this->entityManager->flush();
        return true;
    }
}
