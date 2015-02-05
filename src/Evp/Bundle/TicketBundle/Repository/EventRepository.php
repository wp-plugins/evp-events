<?php
/**
 * Event Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Order;
use Evp\Bundle\TicketBundle\Entity\Ticket;


/**
 * Class EventRepository
 */
class EventRepository extends EntityRepository {

    /**
     * Gets all Events by enabled array
     *
     * @param boolean $enabled
     * @return \Evp\Bundle\TicketBundle\Entity\Event[]
     */
    public function getAllByEnabledStatus($enabled) {
        return $this->_em->createQueryBuilder()
            ->select('ev')
            ->from('Evp\Bundle\TicketBundle\Entity\Event', 'ev')
            ->where('ev.enabled = :vs')
            ->setParameters(array(
                    'vs' => $enabled,
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets one Event by enabled array
     *
     * @param string $eventId
     * @param boolean $enabled
     * @return \Evp\Bundle\TicketBundle\Entity\Event
     */

    public function getOneByIdAndEnabledStatus($eventId, $enabled) {
        return $this->_em->createQueryBuilder()
            ->select('ev')
            ->from('Evp\Bundle\TicketBundle\Entity\Event', 'ev')
            ->where('ev.enabled = :vs')
            ->andWhere('ev.id = :id')
            ->setParameters(array(
                    'vs' => $enabled,
                    'id' => $eventId,
                )
            )
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Gets Event from Ticket
     *
     * @param Ticket $t
     * @return \Evp\Bundle\TicketBundle\Entity\Event
     */
    public function getEventByTicket(Ticket $t) {
        return $t->getEvent();
    }

    /**
     * @param Order $o
     * @return \Evp\Bundle\TicketBundle\Entity\Event
     */
    public function getEventByOrder(Order $o) {
        return $o->getEvent();
    }

    /**
     * Gets Event in DateStarts and DateEnds range
     *
     * @param \DateTime $starts
     * @param \DateTime $ends
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Event[]
     */
    public function getAllByDateStartsAndEnds(\DateTime $starts, \DateTime $ends) {
        return $this->_em->createQueryBuilder()
            ->select('e')
            ->from('Evp\Bundle\TicketBundle\Entity\Event', 'e')
            ->where('e.dateStarts >= :st')
            ->andWhere('e.dateEnds <= :en')
            ->setParameters(
                array(
                    'st' => $starts,
                    'en' => $ends,
                )
            )
            ->getQuery()
            ->getResult();
    }
}
