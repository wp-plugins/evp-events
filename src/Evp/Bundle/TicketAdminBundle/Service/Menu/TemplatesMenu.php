<?php
/**
 * TemplatesMenu for managing Event or TicketType templates
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

/**
 * Class TemplatesMenu
 */
class TemplatesMenu extends MenuAbstract implements MenuInterface {

    const ENTITY_TEMPLATE_FQCN = 'Evp\Bundle\TicketBundle\Entity\Template';
    const PARENT_NAMESPACE = 'Evp\Bundle\TicketBundle\Entity\\';

    /**
     * @var string
     */
    private $templateForm ='Evp\Bundle\TicketBundle\Form\TemplateForm';

    /**
     * @var string
     */
    protected $menuClass = 'Templates';

    /**
     * @var string
     */
    protected $menuTransName = 'templates';

    /**
     * @var object
     */
    private $entity;

    /**
     * @var string
     */
    private $templateType;

    /**
     * @var string
     */
    private $actionName;

    /**
     * @var array
     */
    private $parent;

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
        $this->prepareFormParameters();
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
        $texts = null;
        if (!empty($this->entity)) {
            $parentText = null;
            if ($this->entity instanceof \Evp\Bundle\TicketBundle\Entity\Event) {
                $parentText = 'entity.event.singular';
            } else {
                $parentText = 'entity.ticket_type.singular';
            }
            $texts[] = 'admin.prefix.current';
            $texts[] = $parentText;
            $texts[] = $this->entity->getName();
        }

        return array(
            'submenus' => array_keys($this->generalActions),
            'texts' => $texts,
        );
    }

    /**
     * Prepares and sets the currentAction parameters
     */
    private function prepareFormParameters() {
        $this->parent = $this->currentAction->getParent();
        $this->parent['type'] = $this->templateType;

        $this->currentAction->setParent($this->parent);

        $parentClass = self::PARENT_NAMESPACE .ucfirst($this->parent['class']);
        $this->entity = $this->entityManager->getRepository($parentClass)
            ->findOneBy(array('id' => $this->parent['id']));

        $templateTypes = $this->supplemental['templating']['template_types'];
        foreach ($templateTypes as $key => $type) {
            $templateTypes[$key] = $this->translator->trans($type, array(), 'columns');
        }

        $form = new $this->templateForm;
        $form->setParameters(
            array(
                'locales' => $this->locales,
                'reloadUrl' => $this->getReloadUrlForScope('template', $this->getTarget()),
                'translator' => $this->translator,
                'types' => $templateTypes,
                'currentLocale' => $this->currentLocale,
            )
        );

        $this->currentAction->setParameters(
            array(
                'request' => $this->request,
                'action' => $this->actionName,
                'parent' => $this->entity,
                'form' => $form,
                'fqcn' => self::ENTITY_TEMPLATE_FQCN,
                'templates' => $this->supplemental['templating'],
            )
        );
    }
}
