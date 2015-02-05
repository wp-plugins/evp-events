<?php
/**
 * EventMenu for managing Event actions
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

use Evp\Bundle\TicketBundle\Entity\Event;

/**
 * Class EventMenu
 */
class EventMenu extends MenuAbstract implements MenuInterface {

    const MENU_FQCN = 'Evp\Bundle\TicketBundle\Entity\Event';

    /**
     * @var string
     */
    protected $menuClass = 'Event';

    /**
     * @var string
     */
    protected $menuTransName = 'event';

    /**
     * @var string
     */
    private $menuForm = 'Evp\Bundle\TicketBundle\Form\EventForm';

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

        /** @var Event $event */
        $event = $this->entityManager->find(self::MENU_FQCN, $id);
        $countryCode = $event !== null ? $event->getCountryCode() : $this->defaultCountryCode;

        $form = new $this->menuForm;
        $form->setParameters(
            array(
                'locales' => $this->locales,
                'currencies' => $this->supplemental['currencies'],
                'translator' => $this->translator,
                'reloadUrl' => $this->getReloadUrl($this->menuClass),
                'currentLocale' => $this->currentLocale,
                'countryCodes' => $this->countryCodes,
                'currentCountryCode' => $countryCode,
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
