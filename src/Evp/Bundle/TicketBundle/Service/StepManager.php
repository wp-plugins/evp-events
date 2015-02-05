<?php
/**
 * StepManager for multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Step;
use Evp\Bundle\TicketBundle\EventDispatcher\Event\Step\Changed;
use Evp\Bundle\TicketBundle\EventDispatcher\StepEvents;
use Evp\Bundle\TicketBundle\Exception\StepNotFoundException;
use Evp\Bundle\TicketBundle\Step\StepInterface;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class StepManager
 */
class StepManager extends ManagerAbstract
{
    const BUTTON_NEXT_STEP_LABEL = 'button.next_step';
    const BUTTON_PREVIOUS_STEP_LABEL = 'button.previous_step';
    const BUTTON_CANCEL_LABEL = 'button.cancel';
    const NAVIGATION_FORM_NAME = 'navItem';

    /**
     * \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;

    /**
     * @var \Symfony\Component\Form\FormBuilder
     */
    private $formBuilder;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    private $router;

    /**
     * @var string
     */
    private $currentStepSessionKey;

    /**
     * @var string
     */
    private $previousStepSessionKey;

    /**
     * @var string
     */
    private $defaultStep;


    /**
     * @var StepInterface[]
     */
    private $stepServices;

    /**
     * @var string
     */
    private $eventIdSessionKey;

    /**
     * @var string
     */
    private $userSession;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * StepManager constructor
     *
     * @param \Doctrine\ORM\EntityManager                        $entityManager
     * @param \Monolog\Logger                                    $logger
     * @param string                                             $currentStepSessionKey
     * @param string                                             $previousStepSessionKey
     * @param string                                             $eventIdSessionKey
     * @param string                                             $defaultStep
     * @param \Symfony\Component\HttpFoundation\Session\Session  $session
     * @param \Symfony\Component\Form\FormFactory                $formFactory
     * @param \Symfony\Component\Routing\Router                  $router
     * @param UserSession                                        $userSession
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        $currentStepSessionKey,
        $previousStepSessionKey,
        $eventIdSessionKey,
        $defaultStep,
        Session $session,
        FormFactory $formFactory,
        Router $router,
        UserSession $userSession,
        EventDispatcher $dispatcher
    ) {
        parent::__construct($entityManager, $logger);
        $this->session = $session;
        $this->currentStepSessionKey = $currentStepSessionKey;
        $this->previousStepSessionKey = $previousStepSessionKey;
        $this->eventIdSessionKey = $eventIdSessionKey;
        $this->formBuilder = $formFactory->createBuilder('form', null, array('csrf_protection' => false));
        $this->router = $router;
        $this->defaultStep = $defaultStep;
        $this->userSession = $userSession;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param StepInterface $step
     * @param string        $name
     */
    public function addStepService(StepInterface $step, $name)
    {
        $this->stepServices[$name] =$step;
    }

    /**
     * Extracts current Step
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $event
     * @param string                                $key Step Id
     *
     * @return \Evp\Bundle\TicketBundle\Step\StepInterface
     */
    private function extractCurrentStep(Event $event, $key = null)
    {
        if (!empty($key)) {
            if (array_key_exists($key, $this->stepServices)) {
                return $this->stepServices[$key];
            }
        } else {
            $key = $this->defaultStep;
            $keys = explode('|', $key);
            $this->logger->debug('Exploding default Step Parameters', $keys);
            foreach ($keys as $key) {
                $eventStep = $this->entityManager
                    ->getRepository('EvpTicketBundle:EventTypeStep')
                    ->getEventTypeStepByStep($key, $event->getEventType());
                if (!empty($eventStep)) {
                    if (array_key_exists($key, $this->stepServices)) {
                        return $this->stepServices[$key];
                    }
                }
            }
        }
    }

    /**
     * @param $id
     *
     * @return Event
     */
    public function getEvent($id)
    {
        $event = $this->entityManager->getRepository('EvpTicketBundle:Event')
            ->find($id);
        if (empty($event)) {
            $this->logger->warning('Event not found', array('id' => $id));
            return null;
        }

        return $event;
    }

    /**
     * Checks if Event exists with given Id
     *
     * @param Event $event
     *
     * @return bool
     */
    public function isValidEvent(Event $event)
    {
        if (!$event->getEnabled()) {
            $this->logger->warning('Event not enabled', array('id' => $event->getId()));
            return false;
        }
        if ($event->getDateOnSale() < new DateTime()) {
            $this->logger->warning('Event is not on sale now', array('id' => $event->getId()));
            return false;
        }

        return true;
    }

    /**
     * Returns separate navigation form
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function getStepNavigationForm() {
        return array(
            'cancel' => clone $this->formBuilder
                ->setAction($this->router->generate('evp_ticket_order_cancel', array(), UrlGeneratorInterface::ABSOLUTE_URL))
                ->setMethod('POST')
                ->add(self::NAVIGATION_FORM_NAME, 'submit', array(
                        'label' => self::BUTTON_CANCEL_LABEL,
                    )
                )
                ->getForm()
                ->createView(),
            'next' => clone $this->formBuilder
                ->setAction($this->router->generate('evp_ticket_order_next', array(), UrlGeneratorInterface::ABSOLUTE_URL))
                ->setMethod('POST')
                ->add(self::NAVIGATION_FORM_NAME, 'submit', array(
                        'label' => self::BUTTON_NEXT_STEP_LABEL,
                    )
                )
                ->getForm()
                ->createView()
        );
    }

    /**
     * Gets Event from session stored id
     *
     * @return Event
     */
    public function getEventFromSession()
    {
        return $this->entityManager->getRepository('EvpTicketBundle:Event')
            ->findOneBy(array(
                    'id' => $this->session->get($this->eventIdSessionKey)
                ));
    }

    /**
     * Gets current step Service
     *
     * @param Event $event
     *
     * @return StepInterface
     */
    public function getCurrentStep(Event $event)
    {
        $currentStep = null;
        if ($this->session->has($this->currentStepSessionKey)) {
            if ($this->session->get($this->currentStepSessionKey) != $this->defaultStep) {
                $currentStep = $this->extractCurrentStep($event, $this->session->get($this->currentStepSessionKey));
            } else {
                $currentStep = $this->extractCurrentStep($event);
            }
        } else {
            $currentStep = $this->extractCurrentStep($event);
        }
        $currentStep->setOptions(array(
                'event' => $event,
                'user' => $this->userSession->getUserForThisSession(),
            ));
        return $currentStep;
    }

    /**
     * Returns previous step Service
     *
     * @param \Evp\Bundle\TicketBundle\Step\StepInterface $step
     * @param \Evp\Bundle\TicketBundle\Entity\Event       $event
     *
     * @throws \Evp\Bundle\TicketBundle\Exception\StepNotFoundException
     * @return StepInterface
     */
    public function getPreviousStep(StepInterface $step, Event $event)
    {
        $prevStep = $this->entityManager->getRepository('EvpTicketBundle:EventTypeStep')
            ->getPreviousStepForEventAndCurrentStepName($event, $step->getStepName());

        $this->logger->debug('Got Previous Step Entity', array($prevStep));
        if (!empty($prevStep)) {
            $prevStepName = $prevStep->getSteps()->getParameter();
            if (array_key_exists($prevStepName, $this->stepServices)) {
                $this->logger->debug('Previous Step Service name is ' .$prevStepName);

                $step = $this->stepServices[$prevStepName];
                $step->setOptions(array(
                        'event' => $event,
                        'user' => $this->userSession->getUserForThisSession(),
                    ));
                return $step;
            }
        } else {
            $this->logger->debug('No Previous Step Entity found, suppose this is the first step');
            throw new StepNotFoundException(
                'Previous Step service for step \'' .$step->getStepName() .'\' was not found'
            );
        }
    }

    /**
     * Returns next step Service
     *
     * @param StepInterface $step
     * @param Event         $event
     *
     * @return StepInterface
     * @throws \Evp\Bundle\TicketBundle\Exception\StepNotFoundException
     */
    public function getNextStep(StepInterface $step, Event $event)
    {
        $nextStep = $this->entityManager->getRepository('EvpTicketBundle:EventTypeStep')
            ->getNextStepForEventAndCurrentStepName($event, $step->getStepName());

        $this->logger->debug('Got Next Step Entity', array($nextStep));
        if (!empty($nextStep)) {
            $nextStepName = $nextStep->getSteps()->getParameter();
            if (array_key_exists($nextStepName, $this->stepServices)) {
                $this->logger->debug('Next Step Service name is ' .$nextStepName);

                $step = $this->stepServices[$nextStepName];
                $step->setOptions(array(
                        'event' => $event,
                        'user' => $this->userSession->getUserForThisSession(),
                    ));
                return $step;
            }
        } else {
            $this->logger->debug('No Next Step Entity found, suppose this is the last step');
            throw new StepNotFoundException(
                'Next Step service for step \'' .$step->getStepName() .'\' was not found'
            );
        }
    }

    /**
     * Checks if previous Step was properly finished
     *
     * @param StepInterface                         $currentStep
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $event
     *
     * @return bool
     */
    public function checkPreviousStepCompleted(StepInterface $currentStep, Event $event)
    {
        $this->logger->debug('Checking if Previous Step was completed');
        $prevStepService = null;
        try {
            $prevStepService = $this->getPreviousStep($currentStep, $event);
        } catch (\Exception $e) {
            $this->logger->debug('Previous step not found, suppose its a first step', array($e));
            return true;
        }

        $completed = $prevStepService->isStepCompleted();
        $this->logger->debug('Previous Step is completed: ', array($completed));

        if (!$completed) {
            $this->session->set($this->currentStepSessionKey, $this->getPreviousOrNullStepName($prevStepService));
        }
        return $completed;
    }

    /**
     * Validates given Step against Request
     *
     * @param StepInterface $step
     * @param Request       $request
     *
     * @return array|bool
     */
    public function validateStep(StepInterface $step, Request $request)
    {
        $this->logger->debug(
            'Validating Step service with name \'' .$step->getStepName() .'\' with Request',
            $request->request->all()
        );

        $result = $step->validate($request);
        $this->logger->debug('Step validation result is', array($result));

        return $result;
    }

    /**
     * Saves given Step info from Request
     *
     * @param StepInterface $step
     * @param Request       $request
     *
     * @return bool
     */
    public function saveStepData(StepInterface $step, Request $request)
    {
        $this->logger->debug(
            'Saving data for Step \'' .$step->getStepName() .'\' with Request',
            $request->request->all()
        );

        $saved = $step->save($request);
        $this->logger->debug('Step saving result is', array($saved));

        $this->dispatchOnFirstStep($step);
        $this->dispatchOnNextStep();
        $this->dispatchOnLastStep($step);

        $this->session->set($this->currentStepSessionKey, $this->getNextOrNullStepName($step));

        return $saved;
    }

    /**
     * Gets the name for next step
     *
     * @param StepInterface $step
     *
     * @return null|string
     */
    public function getNextOrNullStepName(StepInterface $step)
    {
        $event = $this->getEventFromSession();
        $nextStep = null;
        try {
            $nextStep = $this->getNextStep($step, $event);
        } catch (StepNotFoundException $e) {
            return null;
        }
        return $nextStep->getStepName();
    }

    /**
     * Gets the name for previous step
     *
     * @param StepInterface $step
     *
     * @return null|string
     */
    public function getPreviousOrNullStepName(StepInterface $step)
    {
        $event = $this->getEventFromSession();
        $nextStep = null;
        try {
            $nextStep = $this->getPreviousStep($step, $event);
        } catch (StepNotFoundException $e) {
            return null;
        }
        return $nextStep->getStepName();
    }

    /**
     * Gets the breadcrumbs object for current Event
     *
     * @param \Evp\Bundle\TicketBundle\Step\StepInterface $step
     * @param bool                                        $ifEnabled
     *
     * @return object|null
     */
    public function getBreadcrumbs(StepInterface $step, $ifEnabled = true)
    {
        $crumbs = new \stdClass;
        if ($ifEnabled) {
            if ($this->getEventFromSession()->getBreadcrumbsEnabled()) {
                $crumbs->currentStep = $this->getStepNumber($step);
                $crumbs->availableSteps = $this->getStepNamesForEvent();
            } else {
                return null;
            }
        } else {
            $crumbs->currentStep = $this->getStepNumber($step);
            $crumbs->availableSteps = $this->getStepNamesForEvent();
        }
        return $crumbs;
    }

    /**
     * @param StepInterface $step
     * @param Event         $event
     *
     * @return StepInterface|null
     */
    public function getNextStepOrNull(StepInterface $step, Event $event)
    {
        $next = null;
        try {
            $next = $this->getNextStep($step, $event);
        } catch (StepNotFoundException $e) {
            return null;
        }
        if ($next->render() !== null) {
            return $next;
        } else {
            return null;
        }
    }

    /**
     * Gets the array of Step name tags for given Event.
     *
     * @return array
     */
    private function getStepNamesForEvent()
    {
        $event = $this->getEventFromSession();
        return $this->entityManager
            ->getRepository('EvpTicketBundle:Step')
            ->getStepNamesForEvent($event);
    }

    /**
     * Gets the number (position) for current step
     *
     * @param \Evp\Bundle\TicketBundle\Step\StepInterface $step
     *
     * @return int
     */
    private function getStepNumber(StepInterface $step)
    {
        return array_search(
            $step->getStepName(),
            $this->getStepNamesForEvent()
        );
    }

    /**
     * Dispatches Event on first step
     *
     * @param StepInterface $step
     */
    private function dispatchOnFirstStep(StepInterface $step)
    {
        $event = $this->getEventFromSession();
        $user = $this->userSession->getUserForThisSession();
        try {
            $this->getPreviousStep($step, $event);
        } catch (StepNotFoundException $e) {
            $this->dispatcher->dispatch(StepEvents::FIRST_STEP_COMPLETED, new Changed($user));
        }
    }

    /**
     * Dispatches Event on next step
     */
    private function dispatchOnNextStep()
    {
        $user = $this->userSession->getUserForThisSession();
        $this->dispatcher->dispatch(StepEvents::NEXT_STEP, new Changed($user));
    }

    /**
     * Dispatches Event on last step
     *
     * @param StepInterface $step
     */
    private function dispatchOnLastStep(StepInterface $step)
    {
        $event = $this->getEventFromSession();
        $user = $this->userSession->getUserForThisSession();
        try {
            $this->getNextStep($step, $event);
        } catch (StepNotFoundException $e) {
            $this->dispatcher->dispatch(StepEvents::LAST_STEP_COMPLETED, new Changed($user));
        }
    }

    /**
     * Dispatches Event on step canceled
     */
    public function dispatchOnStepCanceled()
    {
        $user = $this->userSession->getUserForThisSession();
        $this->dispatcher->dispatch(StepEvents::STEPS_CANCELED, new Changed($user));
    }
}
