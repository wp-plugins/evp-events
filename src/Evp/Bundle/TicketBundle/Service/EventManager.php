<?php
/**
 * EventManager for Event actions through InfoController
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

/**
 * Class EventManager
 */
class EventManager extends ManagerAbstract {
    /**
     * Gets all Events based on enabled status
     *
     * @param bool $enabled
     * @return \Evp\Bundle\TicketBundle\Entity\Event[]
     */
    public function getAllEvents($enabled = true) {
        return $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Event')
            ->getAllByEnabledStatus($enabled);
    }


    /**
     * Gets Event entity by it's Id
     *
     * @param int $eventId
     * @return \Evp\Bundle\TicketBundle\Entity\Event
     */
    public function getEvent($eventId) {
        return $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Event')
            ->find($eventId);
    }
}
