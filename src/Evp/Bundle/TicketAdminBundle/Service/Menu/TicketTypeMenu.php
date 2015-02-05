<?php
/**
 * TicketTypeMenu for managing TicketType actions
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

/**
 * Class TicketTypeMenu
 */
class TicketTypeMenu extends MenuAbstract implements MenuInterface {

    const MENU_FQCN = 'Evp\Bundle\TicketBundle\Entity\TicketType';

    /**
     * @var string
     */
    protected $menuClass = 'TicketType';

    /**
     * @var string
     */
    protected $menuTransName = 'ticket_type';

    /**
     * @var string
     */
    private $actionName;

    /**
     * @var string
     */
    private $menuForm = 'Evp\Bundle\TicketBundle\Form\TicketTypeForm';

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
        $discounts = $this->getDiscountTypesArray();
        $selected = $this->getDiscountTypesArray(false);
        $form->setParameters(
            array(
                'locales' => $this->locales,
                'currencies' => $this->supplemental['currencies'],
                'translator' => $this->translator,
                'discount_types' => $discounts,
                'selected' => $selected,
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


    private function getDiscountTypesArray($allAvailable = true) {
        $types = array();

        if ($allAvailable) {
            $discountTypes = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\DiscountType')
                ->findAll();
            foreach ($discountTypes as $type) {
                $types[$type->getId()] = $type->getName();
            }
            return $types;
        }

        $ticketType = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\TicketType')
            ->findOneBy(
                array(
                    'id' => $this->currentAction->getTarget(),
                )
            );
        if ($ticketType === null) {
            return $types;
        }

        $discountTypes = $ticketType->getDiscountTypes();
        foreach ($discountTypes as $discount) {
            $types[] = $discount->getId();
        }
        return $types;
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
