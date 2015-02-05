<?php
/**
 * Apply a percentage discount to Ticket price
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\DiscountStrategy\DiscountType;

/**
 * Class PercentageType
 */
class PercentageType extends TypeAbstract implements TypeInterface {

    /**
     * Applies a percentage Discount amount from Discount
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Ticket[] $tickets
     * @return bool|void
     */
    public function apply($tickets) {
        foreach ($tickets as $ticket) {
            $newPrice = floatval($ticket->getPrice()) - floatval($ticket->getPrice()) * floatval($this->amount/100);
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
