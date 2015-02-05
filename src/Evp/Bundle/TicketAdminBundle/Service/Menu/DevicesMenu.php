<?php
/**
 * Devices Menu for managing Devices attached to Event
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

use \Evp\Bundle\DeviceApiBundle\Form\TicketExaminerForm;

/**
 * Class TemplatesMenu
 */
class DevicesMenu extends MenuAbstract implements MenuInterface {

    const TICKET_EXAMINER_FQCN = 'Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer';
    const TARGET_ENTITY_FQCN = 'Evp\Bundle\TicketBundle\Entity\Event';

    /**
     * @var string
     */
    protected $menuClass = 'Devices';

    /**
     * @var string
     */
    protected $menuTransName = 'devices';

    /**
     * @var object
     */
    private $entity;

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

        $form = new TicketExaminerForm;
        $form->setParameters(
            array(
                'translator' => $this->translator,
            )
        );

        $this->currentAction->setParameters(
            array(
                'fqcn' => self::TICKET_EXAMINER_FQCN,
                'form' => $form,
                'request' => $this->request,
                'actionName' => $this->actionName,
            )
        );
    }

    /**
     * Array of Twig parameters for particular Menu Action
     *
     * @return array
     */
    public function getResponseParameters() {
        if ($this->currentAction->getResponseType() === self::RESPONSE_REGULAR) {
            $actions = array_keys($this->commonActions);
            $actions = array_diff($actions, array($this->currentAction->getName()));

            return array(
                'elements' => $this->currentAction->buildResponseParameters(),
                'actions' => $actions,
                'menuAlias' => $this->menuClass,
            );
        }
        if ($this->currentAction->getResponseType() === self::RESPONSE_REDIRECT) {
            return $this->currentAction->buildResponseParameters();
        }
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
            $texts[] = 'entity.event.singular';
            $texts[] = $this->entity->getName();
        }

        return array(
            'submenus' => array_keys($this->generalActions),
            'texts' => $texts,
        );
    }
}
