<?php
/**
 * TicketType Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Evp\Bundle\TicketBundle\Entity\Order;

/**
 * Class TicketType
 */
class TicketTypeRepository extends EntityRepository {

    /**
     * Returns TicketTypes by Event
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @param bool $status
     * @return \Evp\Bundle\TicketBundle\Entity\TicketType[]
     */
    public function getAllActiveByEvent(Event $ev, $status = true) {
        return $this->_em->createQueryBuilder()
            ->select('tt')
            ->from('Evp\Bundle\TicketBundle\Entity\TicketType', 'tt')
            ->where('tt.event = :ev')
            ->andWhere('tt.status = :st')
            ->setParameters(
                array (
                    'ev' => $ev,
                    'st' => $status
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets all active & available TicketTypes for given Event
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @return \Evp\Bundle\TicketBundle\Entity\TicketType[]
     */
    public function getAllActiveAndAvailableByEvent(Event $ev) {
        $types = $this->getAllActiveByEvent($ev);
        $available = array();
        foreach ($types as $type) {
            $count = $this->getAvailableCountByEventAndTicketType($ev, $type);
            if ($count === null || $count > 0) {
                $available[] = $type;
            }
        }
        return $available;
    }

    /**
     * Gets Available tickets count by Event & TicketType
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @param \Evp\Bundle\TicketBundle\Entity\TicketType $tt
     *
     * @return int
     */
    public function getAvailableCountByEventAndTicketType(Event $ev, TicketType $tt) {
        $ticketsUsed = $this->_em->createQueryBuilder()
            ->select('COUNT(t)')
            ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
            ->innerJoin('t.orderDetails', 'od')
            ->innerJoin('od.order', 'o')
            ->where('t.event = :ev')
            ->andWhere('t.ticketType = :tt')
            ->andWhere('o.status != :orderStatus')
            ->setParameters(
                array (
                    'ev' => $ev,
                    'tt' => $tt,
                    'orderStatus' => Order::STATUS_EXPIRED,
                )
            )
            ->getQuery()
            ->getSingleScalarResult();

        if ($tt->getTicketsCount() !== null) {
            return intval($tt->getTicketsCount()) - intval($ticketsUsed);
        }
        return null;
    }

    /**
     * Gets all TicketTypes by valid Event
     * @return \Evp\Bundle\TicketBundle\Entity\TicketType[]
     */
    public function getAllByValidEvent() {
        return $this->_em->createQueryBuilder()
            ->select('tt')
            ->from('Evp\Bundle\TicketBundle\Entity\TicketType', 'tt')
            ->join('tt.event', 'ev')
            ->where('ev.enabled = :tr')
            ->andWhere('ev.dateEnds >= :now')
            ->setParameters(
                array(
                    'tr' => true,
                    'now' => new \DateTime,
                )
            )
            ->getQuery()
            ->getResult();
    }
}
