<?php
/**
 * EventTypeMenu for managing EventType actions
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

/**
 * Class EventTypeMenu
 */
class EventTypeMenu extends MenuAbstract implements MenuInterface {

    const MENU_FQCN = 'Evp\Bundle\TicketBundle\Entity\EventType';

    /**
     * @var string
     */
    protected $menuClass = 'EventType';

    /**
     * @var string
     */
    protected $menuTransName = 'event_type';

    /**
     * @var string
     */
    private $menuForm = 'Evp\Bundle\TicketBundle\Form\EventTypeForm';

    /**
     * @var string
     */
    private $actionName;

    /**
     * Sets current Action name
     *
     * @param string $action
     */
    public function setCurrentAction($action) {
        $this->currentAction = $this->actions[$action];
        $this->actionName = $action;

    }

    /**
     * Sets the Target for currentAction
     *
     * @param string $id
     */
    public function setTarget($id) {
        $this->currentAction->setTarget($id);

        $form = new $this->menuForm;
        $form->setParameters(
            array(
                'locales' => $this->locales,
                'translator' => $this->translator,
                'reloadUrl' => $this->getReloadUrl($this->menuClass),
                'currentLocale' => $this->currentLocale,
            )
        );

        $this->currentAction->setParameters(
            array(
                'fqcn' => self::MENU_FQCN,
                'form' => $form,
                'request' => $this->request,
                'actionName' => $this->actionName,
            )
        );
    }

    /**
     * Gets the sub-menus by [action] = [translation.tag] pattern
     *
     * @return array
     */
    public function getSpecificMenuItems() {
        return array(
            'submenus' => array_keys($this->generalActions),
            'texts' => array(),
        );
    }
}
