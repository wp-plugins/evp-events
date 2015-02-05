<?php
/**
 * EventTypeFieldSchemaMenu for managing EventTypeFieldSchema actions for particular EventType
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

/**
 * Class EventTypeFieldSchemaMenu
 */
class EventTypeFieldSchemaMenu extends MenuAbstract implements MenuInterface {

    const MENU_FQCN = 'Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema';
    const TARGET_ENTITY_FQCN = 'Evp\Bundle\TicketBundle\Entity\EventType';

    /**
     * @var string
     */
    protected $menuClass = 'EventTypeFieldSchema';

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\EventType
     */
    private $entity;

    /**
     * @var string
     */
    protected $menuTransName = 'event_type_field_schema';

    /**
     * @var string
     */
    private $menuForm = 'Evp\Bundle\TicketBundle\Form\EventTypeFieldSchemaForm';

    /**
     * Sets current Action name
     *
     * @param string $action
     */
    public function setCurrentAction($action) {
        $this->currentAction = $this->actions[$action];
        $form = new $this->menuForm;
        $form->setParameters(
            array(
                'translator' => $this->translator,
            )
        );
        $this->currentAction->setParameters(

            array(
                'fqcn' => self::MENU_FQCN,
                'form' => $form,
                'request' => $this->request,
            )
        );
    }

    /**
     * Gets the sub-menus by [action] = [translation.tag] pattern
     *
     * @return array
     */
    public function getSpecificMenuItems() {
        $parent = $this->currentAction->getParent();
        $this->entity = $this->entityManager->getRepository(self::TARGET_ENTITY_FQCN)
            ->findOneBy(array('id' => $parent['id']));
        $texts = null;
        if (!empty($this->entity)) {
            $texts[] = 'admin.prefix.current';
            $texts[] = 'entity.event_type.singular';
            $texts[] = $this->entity->getName();
        }

        return array(
            'submenus' => array_keys($this->generalActions),
            'texts' => $texts,
        );
    }
}
