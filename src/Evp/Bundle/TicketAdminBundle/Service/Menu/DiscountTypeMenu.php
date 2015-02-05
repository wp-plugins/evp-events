<?php
/**
 * DiscountTypeMenu for managing DiscountType actions
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

/**
 * Class DiscountTypeMenu
 */
class DiscountTypeMenu extends MenuAbstract implements MenuInterface {

    const MENU_FQCN = 'Evp\Bundle\TicketBundle\Entity\DiscountType';

    /**
     * @var string
     */
    protected $menuClass = 'DiscountType';

    /**
     * @var string
     */
    protected $menuTransName = 'discount_type';

    /**
     * @var string
     */
    private $menuForm = 'Evp\Bundle\TicketBundle\Form\DiscountTypeForm';

    /**
     * Sets current Action name
     *
     * @param string $action
     */
    public function setCurrentAction($action) {
        $this->currentAction = $this->actions[$action];

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
                'currencies' => $this->supplemental['currencies'],
                'translator' => $this->translator,
                'discountStrategies' => $this->supplemental['discountStrategies'],
                'discountScopes' => $this->supplemental['discountScopes'],
                'discountTypes' => $this->supplemental['discountTypes'],
                'reloadUrl' => $this->getReloadUrl($this->menuClass),
                'currentLocale' => $this->currentLocale,
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
