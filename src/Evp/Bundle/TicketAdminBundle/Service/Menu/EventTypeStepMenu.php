<?php
/**
 * EventTypeStepMenu for managing EventTypeStep actions for particular EventType
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

use Evp\Bundle\TicketBundle\Entity\EventType;

/**
 * Class EventTypeStepMenu
 */
class EventTypeStepMenu extends MenuAbstract implements MenuInterface {

    const MENU_FQCN = 'Evp\Bundle\TicketBundle\Entity\EventTypeStep';
    const TARGET_ENTITY_FQCN = 'Evp\Bundle\TicketBundle\Entity\EventType';

    /**
     * @var string
     */
    protected $menuClass = 'EventTypeStep';

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\EventType
     */
    private $entity;

    /**
     * @var string
     */
    protected $menuTransName = 'event_type_step';

    /**
     * @var string
     */
    private $menuForm = 'Evp\Bundle\TicketBundle\Form\EventTypeStepForm';

    /**
     * @var string[]
     */
    private $requiredSteps;

    /**
     * @var string[]
     */
    private $conflictingSteps;

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
                'translator' => $this->translator,
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
     * @param array $required
     * @param array $conflicting
     */
    public function setStepMaps($required, $conflicting)
    {
        $this->requiredSteps = $required;
        $this->conflictingSteps = $conflicting;
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
            $conflict = $this->validateStepsConfiguration($this->entity);
            $translatedConflict = $this->translator->trans($conflict, array(), 'validators');

            $texts[] = 'admin.prefix.current';
            $texts[] = 'entity.event_type.singular';
            $texts[] = $this->entity->getName();
            $texts[] = '<div class="stepConflict">' .$translatedConflict .'</div>';
        }

        return array(
            'submenus' => array_keys($this->generalActions),
            'texts' => $texts,
        );
    }

    /**
     * Validates steps in current configuration
     *
     * @param EventType $eventType
     *
     * @return bool|string
     */
    private function validateStepsConfiguration(EventType $eventType)
    {
        $steps = $this->entityManager->getRepository('EvpTicketBundle:EventTypeStep')
            ->findBy(
                array(
                    'eventType' => $eventType,
                )
            );
        $stepIds = array();
        foreach ($steps as $step) {
            $stepIds[] = $step->getSteps()->getParameter();
        }
        $missingSteps = array();
        foreach ($this->requiredSteps as $required) {
            $needed = explode('|', $required);
            $found = false;
            foreach ($stepIds as $name) {
                if (!in_array($name, $needed)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingSteps[] = $required;
            }
        }
        if (!empty($missingSteps)) {
            $this->logger->addError('Missing Steps in Event configuration', $missingSteps);
            return 'missing_step.' .reset($missingSteps);
        }
        $conflict = $this->resolveStepConflicts($stepIds);
        if (!empty($conflict)) {
            $this->logger->addError('Step conflict detected', array($conflict));
            return $conflict;
        }
        return false;
    }

    /**
     * Checks if current step config has conflicts, returns conflict tag or empty
     *
     * @param array $stepIds
     *
     * @return string
     */
    private function resolveStepConflicts($stepIds)
    {
        foreach ($stepIds as $id) {
            if (array_key_exists($id, $this->conflictingSteps)) {
                $conflicted = $this->conflictingSteps[$id];
                foreach ($conflicted as $param) {
                    if (in_array($param, $stepIds)) {
                        return 'step_conflict.' . $id . '.x.' . $param;
                    }
                }
            }
        }
        return null;
    }
}
