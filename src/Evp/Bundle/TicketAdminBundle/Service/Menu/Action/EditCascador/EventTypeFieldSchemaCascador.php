<?php
/**
 * Class for Cascade actions on EventTypeFieldSchema Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\EditCascador;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;

/**
 * Class EventTypeFieldSchemaCascador
 */
class EventTypeFieldSchemaCascador extends ActionAbstract implements CascadorInterface {

    /**
     * @var string
     */
    private $entityClass = 'Evp\Bundle\TicketBundle\Entity\EventType';

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema
     */
    private $schemaMap;

    /**
     * Cascades the specific relations
     * @param object $obj
     */
    public function cascade($obj) {
        $this->schemaMap = $obj;
        $parent = $this->getParent();
        $eventType = $this->entityManager->getRepository($this->entityClass)
            ->findOneBy(
                array(
                    'id' => $parent['id'],
                )
            );
        $this->schemaMap->setEventType($eventType);
        $this->entityManager->flush($this->schemaMap);
    }
}
