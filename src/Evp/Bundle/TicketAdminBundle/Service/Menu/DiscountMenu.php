<?php
/**
 * DiscountMenu for managing Discount actions
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

/**
 * Class DiscountMenu
 */
class DiscountMenu extends MenuAbstract implements MenuInterface {

    const MENU_FQCN = 'Evp\Bundle\TicketBundle\Entity\Discount';

    /**
     * @var string
     */
    protected $menuClass = 'Discount';

    /**
     * @var string
     */
    protected $menuTransName = 'discount';

    /**
     * @var string
     */
    private $menuForm = 'Evp\Bundle\TicketBundle\Form\DiscountForm';

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
                'locales' => $this->locales,
                'currencies' => $this->supplemental['currencies'],
                'translator' => $this->translator,
                'reloadUrl' => 0
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
        return array(
            'submenus' => array_keys($this->generalActions),
            'texts' => array(),
        );
    }
}
