<?php
/**
 * StepController for multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Controller;

use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\EventDispatcher\Event\Step\Changed;
use Evp\Bundle\TicketBundle\EventDispatcher\StepEvents;
use Evp\Bundle\TicketBundle\Exception\StepNotFoundException;
use Evp\Bundle\TicketBundle\Service\StepManager;
use Evp\Bundle\TicketBundle\Step\StepInterface;
use Evp\Bundle\TicketMaintenanceBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class StepController
 */
class StepController extends RedirectController
{
    const TWIG_ERROR_TEMPLATE = 'EvpTicketBundle:Step:errors.html.twig';

    /**
     * Makes basic redirections based on Session & Request data
     *
     * @param string $eventId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($eventId = null) {
        if (!$eventId) {
            $this->get('evp.service.user_session')->destroyCurrentSession(false);
            return $this->render(
                self::TWIG_ERROR_TEMPLATE,
                array('errors' => array('No event selected!'))
            );
        }

        $stepManager = $this->get('evp.service.step_manager');
        $event = $stepManager->getEvent($eventId);

        if ($event === null) {
            return $this->redirect($this->generateUrl('evp_ticket_event_list'));
        } elseif (!$stepManager->isValidEvent($event)) {
            return $this->redirect($this->generateUrl('evp_ticket_event_info_inactive', array('eventId' => $event->getId())));
        }

        $session = $this->get('session');
        $session->start();

        $eventSessionKey = $this->container->getParameter('evp.service.event_id_session_key');
        $stepSessionKey = $this->container->getParameter('evp.service.step.current_step_session_key');

        $session->set($eventSessionKey, $eventId);
        $session->set(
            $stepSessionKey,
            $this->container->getParameter('evp.service.step.default_step')
        );

        $this->get('evp.service.user_session')->destroyCurrentSession(false);
        $this->get('evp.service.user_session')->createNewUserForThisEvent();


        return $this->redirect($this->generateUrl($this->container->getParameter('evp.router.next_step')));
    }

    /**
     * Checks Step Validity and proceeds to next step if OK
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function nextAction(Request $request)
    {
        $logger = $this->get('logger');
        $stepManager = $this->get('evp.service.step_manager');

        if ($this->isSessionLost()) {
            return $this->generateSessionTimeoutErrorResponse();
        }

        if (!$this->get('evp.service.user_session')->isCurrentUserValid()) {
            $logger->debug('Found an expired session');
            return $this->forward('EvpTicketBundle:Step:cancel');
        }

        $event = $stepManager->getEventFromSession();
        $currentStep = $stepManager->getCurrentStep($event);
        $previousCompleted = $stepManager->checkPreviousStepCompleted($currentStep, $event);

        if (!$previousCompleted) {
            $currentStep = $stepManager->getPreviousStep($currentStep, $event);
            $request->request->replace();
        }

        $validationErrors = null;
        if ($request->request->count() != 0) {
            $validationErrors = $stepManager->validateStep($currentStep, $request);
            if (empty($validationErrors)) {
                if ($stepManager->saveStepData($currentStep, $request)) {
                    $currentStep = $stepManager->getNextStepOrNull($currentStep, $event);
                    if (empty($currentStep)) {
                        return $this->forward('EvpPaymentBundle:Payment:redirectToPayment',
                            array(
                                '_locale' => $request->getLocale()
                            )
                        );
                    }
                }
            }
        }

        return $this->render(
            $currentStep->getTemplate(),
            array(
                'elements' => $currentStep->render(),
                'navigation' => $stepManager->getStepNavigationForm(),
                'errors' => $validationErrors,
                'breadcrumbs' => $stepManager->getBreadcrumbs($currentStep),
            )
        );
    }

    /**
     * @return RedirectResponse
     */
    public function cancelAction()
    {
        if ($this->isSessionLost()) {
            return $this->generateSessionTimeoutErrorResponse();
        }

        if ($this->get('evp.service.user_session')->isCurrentUserValid()) {
            $this->get('evp.service.step_manager')->dispatchOnStepCanceled();
        }

        $this->get('session')->remove($this->container->getParameter('evp.service.step.current_step_session_key'));
        $this->get('session')->remove($this->container->getParameter('evp.service.step.previous_step_session_key'));
        $eventId = $this->get('session')->remove('eventId');
        $this->get('evp.service.user_session')->destroyCurrentSession();

        return $this->redirect($this->generateUrl('evp_ticket_event_info', array('eventId' => $eventId)));
    }

    /**
     * Is there no information attached to the user session about the step he is supposed to be?
     *
     * @return bool
     */
    private function isSessionLost()
    {
        $session = $this->get('session');
        return !$session->has($this->container->getParameter('evp.service.event_id_session_key'))
        || !$session->has($this->container->getParameter('evp.service.step.current_step_session_key'));
    }

    /**
     * @return RedirectResponse
     */
    private function generateSessionTimeoutErrorResponse()
    {
        return $this->redirect($this->generateUrl('display_error', array('message' => 'error.session_timeout')));
    }
}
