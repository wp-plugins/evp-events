<?php
/**
 * EventType Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Event;


/**
 * Class EventType
 */
class EventTypeRepository extends EntityRepository {

    public function getEventTypeByEvent(Event $event) {
        return $this->_em->createQueryBuilder()
            ->select('et')
            ->from('Evp\Bundle\TicketBundle\Entity\EventType', 'et')
            ->join('et.event', 'ev', 'WITH', 'ev = :ev')
            ->setParameters(
                array(
                    'ev' => $event,
                )
            )
            ->getQuery()
            ->getOneOrNullResult();

    }
}
