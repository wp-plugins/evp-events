<?php
/**
 * Sets fixed Ticket Price
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\DiscountStrategy\DiscountType;

/**
 * Class FixedPriceType
 */
class FixedPriceType extends TypeAbstract implements TypeInterface {

    /**
     * Sets a fixed price amount from Discount
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Ticket[] $tickets
     * @return bool|void
     */
    public function apply($tickets) {
        foreach ($tickets as $ticket) {
            $ticket->setPrice(
                floatval($this->amount)
            );
            $ticket->setDateModified(new \DateTime);
            $this->logger->debug('applied discount data', array('discount' => $this->amount, 'ticket' => $ticket));
            $this->entityManager->persist($ticket);
        }
        $this->entityManager->flush();
        return true;
    }
}
