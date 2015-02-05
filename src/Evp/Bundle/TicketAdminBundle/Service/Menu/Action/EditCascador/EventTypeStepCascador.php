<?php
/**
 * Class for Cascade actions on EventTypeStep Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\EditCascador;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;

/**
 * Class EventTypeStepCascador
 */
class EventTypeStepCascador extends ActionAbstract implements CascadorInterface {

    /**
     * @var string
     */
    private $entityClass = 'Evp\Bundle\TicketBundle\Entity\EventType';

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\EventTypeStep
     */
    private $eventTypeStep;

    /**
     * Cascades the specific relations
     * @param object $obj
     */
    public function cascade($obj) {
        $this->eventTypeStep = $obj;
        $parent = $this->getParent();
        $eventType = $this->entityManager->getRepository($this->entityClass)
            ->findOneBy(
                array(
                    'id' => $parent['id'],
                )
            );
        $this->eventTypeStep->setEventType($eventType);
        $this->entityManager->flush($this->eventTypeStep);
    }
}
