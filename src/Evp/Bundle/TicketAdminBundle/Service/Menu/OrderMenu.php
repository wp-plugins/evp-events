<?php
/**
 * OrderMenu for managing Order actions
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;

/**
 * Class OrderMenu
 */
class OrderMenu extends MenuAbstract implements MenuInterface {

    const MENU_FQCN = 'Evp\Bundle\TicketBundle\Entity\Order';
    const MENU_FORM_FQCN = 'Evp\Bundle\TicketBundle\Entity\Step\OrderDetails';
    /**
     * @var string
     */
    protected $menuClass = 'Order';

    /**
     * @var string
     */
    protected $menuTransName = 'order';

    /**
     * @var string
     */
    private $menuForm = 'Evp\Bundle\TicketBundle\Form\OrderDetailsForm';

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
        $events = $this->entityManager
            ->createQueryBuilder('e')
            ->select('count(e)')
            ->from('Evp\Bundle\TicketBundle\Entity\Event', 'e')
            ->where('e.dateEnds >= :now')
            ->setParameter('now', new \DateTime)
            ->getQuery()
            ->getSingleScalarResult();

        $emptyText = $events > 0
            ? $this->translator->trans(OrderDetails::VALUE_HAS_EVENTS, array(), 'columns')
            : $this->translator->trans(OrderDetails::VALUE_NO_EVENTS, array(), 'columns');

        $this->currentAction->setTarget($id);
        $form = new $this->menuForm;
        $form->setParameters(
            array(
                'locales' => $this->locales,
                'fieldTypes' => $this->supplemental['fieldTypes'],
                'translator' => $this->translator,
                'reloadUrl' => $this->getReloadUrlForScope('events'),
                'emptyText' => $emptyText,
                'disableSubmit' => $events > 0 ? false : true,
            )
        );
        $formObj = $this->actionName == self::ACTION_EDIT || $this->actionName == self::ACTION_ADD
            ? $formObj = self::MENU_FORM_FQCN
            : $formObj = self::MENU_FQCN;
        $this->currentAction->setParameters(
            array(
                'fqcn' => $formObj,
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
