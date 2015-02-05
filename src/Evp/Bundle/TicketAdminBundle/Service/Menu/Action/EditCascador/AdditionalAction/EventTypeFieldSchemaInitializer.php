<?php
/**
 * Class adds initial data to EventTypeFieldSchema Map on new EventType
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\EditCascador\AdditionalAction;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\EditCascador\CascadorInterface;
use Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema;

/**
 * Class EventTypeFieldSchemaInitializer
 */
class EventTypeFieldSchemaInitializer extends ActionAbstract implements CascadorInterface {

    /**
     * @var string
     */
    private $evenTypeFieldSchema = 'Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema';

    /**
     * @var string
     */
    private $fieldSchema = 'Evp\Bundle\TicketBundle\Entity\Form\FieldSchema';

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\EventType
     */
    private $eventType;

    /**
     * Cascades the specific relations
     * @param object $obj
     */
    public function cascade($obj) {
        $this->eventType = $obj;
        $this->updateEventTypeFieldSchema();
    }

    private function updateEventTypeFieldSchema()
    {
        $generalEmail = $this->entityManager->getRepository($this->fieldSchema)
            ->find(1);

        $eventTypeFieldSchemas = $this->entityManager->getRepository($this->evenTypeFieldSchema)
            ->findOneBy(
                array(
                    'eventType' => $this->eventType,
                    'isRequired' => true,
                    'isMadeByAdmin' => false,
                    'isRequiredForAll' => false,
                    'fieldSchema' => $generalEmail,
                )
            );
        if (empty($eventTypeFieldSchemas)) {
            $eventTypeFieldSchema = new EventTypeFieldSchema;

            $eventTypeFieldSchema->setEventType($this->eventType);
            $eventTypeFieldSchema->setFieldSchema($generalEmail);
            $eventTypeFieldSchema->setIsRequired(1);
            $eventTypeFieldSchema->setIsMadeByAdmin(0);
            $eventTypeFieldSchema->setIsRequiredForAll(0);

            $this->entityManager->persist($eventTypeFieldSchema);
            $this->entityManager->flush();
        }
    }
}
