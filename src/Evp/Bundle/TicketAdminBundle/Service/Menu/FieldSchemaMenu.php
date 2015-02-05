<?php
/**
 * FieldSchemaMenu for managing Field Schema actions
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

/**
 * Class FieldSchemaMenu
 */
class FieldSchemaMenu extends MenuAbstract implements MenuInterface {

    const MENU_FQCN = 'Evp\Bundle\TicketBundle\Entity\Form\FieldSchema';

    /**
     * @var string
     */
    protected $menuClass = 'FieldSchema';

    /**
     * @var string
     */
    protected $menuTransName = 'field_schema';

    /**
     * @var string
     */
    private $menuForm = 'Evp\Bundle\TicketBundle\Form\FieldSchemaForm';

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
                'fieldTypes' => $this->supplemental['fieldTypes'],
                'translator' => $this->translator,
                'reloadUrl_validators' => $this->getReloadUrlForScope('field_types'),
                'reloadUrl_locales' => $this->getReloadUrl($this->menuClass),
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
