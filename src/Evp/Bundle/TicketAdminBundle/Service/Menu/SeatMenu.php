<?php
/**
 * SeatMenu for managing Seat schemas
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

/**
 * Class SeatMenu
 */
class SeatMenu extends MenuAbstract implements MenuInterface {

    const MENU_FQCN = 'Evp\Bundle\TicketBundle\Entity\Seat\Area';

    /**
     * @var string
     */
    private $formClass = 'Evp\Bundle\TicketBundle\Form\Seat\AreaForm';

    /**
     * @var string
     */
    protected $menuClass = 'Seat';

    /**
     * @var string
     */
    protected $menuTransName = 'seat';

    /**
     * @var string
     */
    private $actionName;

    /**
     * @var array
     */
    private $children = array(
        'ticketType',
    );

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

        $form = new $this->formClass;
        $parent = $this->currentAction->getParent();
        $form->setParameters(
            array(
                'translator' => $this->translator,
                'is_parent' => in_array($parent['class'], $this->children),
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
