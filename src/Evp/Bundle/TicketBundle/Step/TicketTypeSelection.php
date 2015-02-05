<?php
/**
 * TicketTypeSelection step from multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;
use Evp\Bundle\TicketBundle\Service\TicketManager;
use Evp\Bundle\TicketBundle\Step\Forms\TicketTypeSelectionForm;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class TicketTypeSelection
 * Generates step, where user can input ticket count for various ticket types
 */
class TicketTypeSelection extends StepAbstract implements StepInterface {

    const ADD_TO_CART_BUTTON_NAME = 'addToCart';
    const ADD_TO_CART_BUTTON_LABEL = 'button.add_to_cart';
    const TICKET_TYPE_ID_HIDDEN_NAME = 'ticketType';
    const TICKET_COUNT_FIELD_NAME = 'ticketsCount';
    const KEY_AVAILABLE = 'available';
    const KEY_REQUESTED = 'requested';

    const STEP_TICKET_TYPE_SELECTION = 'ticket_type_selection';

    /**
     * @var \Symfony\Component\Form\AbstractType
     */
    private $form;

    /**
     * @var
     */
    private $validation;

    /**
     * @var array
     */
    private $formBody = array();

    /**
     * @var string Twig template
     */
    protected $template;

    /**
     * {@inheritdoc}
     *
     * @param array $params
     */
    public function setParams($params) {
        $this->template = $params['template'];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getStepName()
    {
        return self::STEP_TICKET_TYPE_SELECTION;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isStepCompleted()
    {
        $orderDetails = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findOneBy(array(
                    'user' => $this->user,
                    'event' => $this->event,
                ));
        return !empty($orderDetails);
    }

    /**
     * Validates step data
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array|bool|null
     */
    public function validate(Request $request) {
        $form = $this->parseRequest($request);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $orderDetails = $this->entityManager
                ->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
                ->findOneBy(array(
                        'user' => $this->user,
                        'event' => $this->event,
                    ));
            if(!empty($orderDetails)) {
                return null;
            }
            return true;
        }

        if (!$form->isValid()) {
            $errors = array();
            foreach ($form->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            return $errors;
        }
    }

    /**
     * Returns response
     *
     * @return mixed
     */
    public function render() {
        $this->formBody = array();
        $this->prepareFormBody();

        return $this->formBody;
    }

    /**
     * Prepares formBody
     */
    public function prepareFormBody() {
        $availableTicketTypes = $this->entityManager
            ->getRepository('EvpTicketBundle:TicketType')
            ->getAllActiveAndAvailableByEvent($this->event);

        foreach ($availableTicketTypes as $ticketType) {
            $availableTicketsCnt = $this->entityManager
                ->getRepository('EvpTicketBundle:TicketType')
                ->getAvailableCountByEventAndTicketType($this->event, $ticketType);
            $orderDetails = new OrderDetails;
            $orderDetails->setTicketTypeName($ticketType->getName());
            $orderDetails->setCurrency($this->event->getCurrency());
            $orderDetails->setPrice($ticketType->getPrice());
            $orderDetails->setTicketType($ticketType->getId());
            $orderDetails->setTicketsLeft($availableTicketsCnt);
            $this->form->setTicketType($ticketType);
            $this->formBody[self::KEY_AVAILABLE][] = $this->formFactory->create($this->form, $orderDetails)->createView();
        }

        $requestedOrderDetails = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->getAllByUserAndEvent($this->user, $this->event);
        if (!empty($requestedOrderDetails)) {
            $this->formBody[self::KEY_REQUESTED] = $requestedOrderDetails;
        }
    }

    /**
     * Saves step data
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    public function save(Request $request) {
        $formOrderDetails = new OrderDetails;
        $form = $this->parseRequest($request, $formOrderDetails);
        $form->handleRequest($request);

        if ($form->get(self::ADD_TO_CART_BUTTON_NAME)->isClicked()) {
            $ticketType = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\TicketType')
                ->findOneBy(array('id' => $form->get(self::TICKET_TYPE_ID_HIDDEN_NAME)->getData()));

            $orderDetails = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
                ->findOneBy(
                    array(
                        'event' => $this->event,
                        'ticketType' => $ticketType,
                        'user' => $this->user,
                    )
                );

            if (empty($orderDetails)) {
                $formOrderDetails->setEvent($this->event);
                $formOrderDetails->setUser($this->user);
                $formOrderDetails->setOrder($this->user->getOrder());
                $formOrderDetails->setTicketType($ticketType);

                $this->entityManager->persist($formOrderDetails);
                $this->entityManager->flush();

                $this->ticketManager->createTickets(
                    $this->event,
                    $ticketType,
                    $formOrderDetails,
                    $this->user,
                    $formOrderDetails->getTicketsCount()
                );
            } else {
                $orderDetails->setTicketsCount(
                    $orderDetails->getTicketsCount() + $formOrderDetails->getTicketsCount()
                );

                $this->entityManager->persist($orderDetails);
                $this->entityManager->flush();

                $this->ticketManager->createTickets(
                    $this->event,
                    $ticketType,
                    $orderDetails,
                    $this->user,
                    $formOrderDetails->getTicketsCount()
                );
            }
            return false;
        }
        return true;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails $orderDetails
     * @return \Symfony\Component\Form\FormFactory
     */
    public function parseRequest(Request $request, OrderDetails $orderDetails = null) {
        if (empty($orderDetails)) {
            $orderDetails = new OrderDetails;
        }
        if ($request->request->has($this->form->getName())) {
            if (array_key_exists(self::TICKET_TYPE_ID_HIDDEN_NAME,
                $request->request->get($this->form->getName()))
            ) {
                $formData = $request->request->get($this->form->getName());
                $ticketType = $this->entityManager
                    ->getRepository('Evp\Bundle\TicketBundle\Entity\TicketType')
                    ->findOneBy(array(
                            'id' => $formData[self::TICKET_TYPE_ID_HIDDEN_NAME],
                        )
                    );
                $this->form->setTicketType($ticketType);
            }
        }
        return $this->formFactory->create($this->form, $orderDetails);
    }

    /**
     * Sets required options
     *
     * @param array $options
     * @return self
     */
    public function setOptions($options) {
        $this->event = $options['event'];
        $this->user = $options['user'];
        $this->form = new TicketTypeSelectionForm($this->event, $this->user);

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate() {
        return $this->template;
    }
}
