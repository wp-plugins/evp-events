<?php
/**
 * Gives functionality to choose tickets & apply discount in one step.
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step;

use Evp\Bundle\TicketBundle\Entity\Step\DiscountCode;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;
use Evp\Bundle\TicketBundle\Service\TicketManager;
use Evp\Bundle\TicketBundle\Step\Forms\DiscountCodeForm;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketTypeDiscount
 * @package Evp\Bundle\TicketBundle\Step
 */
class TicketTypeDiscount extends StepAbstract implements StepInterface
{
    const MAX_TICKETS_ALLOWED = 50;

    const STEP_TICKET_TYPE_DISCOUNT = 'ticket_type_discount';

    /**
     * @var string
     */
    private $template;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\UserSession
     */
    private $userSession;

    /**
     * @var FormFactory
     */
    private $form;

    /**
     * @var ApplyDiscount
     */
    private $applyDiscountStep;

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
     * {@inheritdoc}
     *
     * @return string
     */
    public function getStepName()
    {
        return self::STEP_TICKET_TYPE_DISCOUNT;
    }

    /**
     * {@inheritdoc}
     *
     * @param Request $request
     *
     * @return array|bool|void
     */
    public function validate(Request $request)
    {
        $discountCodeForm = new DiscountCodeForm($this->event, $this->user);
        $discountCode = new DiscountCode;

        $this->form = $this->formFactory->create($discountCodeForm, $discountCode);
        $this->form->handleRequest($request);
        if ($this->form->isSubmitted() && $this->form->getName() == $discountCodeForm->getName()) {
            $validationResult = $this->applyDiscountStep->validate($request);
            if ($validationResult != null) {
                $this->form->addError(new FormError($validationResult));
            }
            return $validationResult;
        } else {
            $orderDetails = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
                ->findBy(
                    array(
                        'user' => $this->userSession->getUserForThisSession(),
                    )
                );
            if (!empty($orderDetails)) {
                $this->applyDiscountStep->setSkipSave(true);
                return null;
            }
            return true;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Event
     */
    public function render()
    {
        $types = $this->entityManager
            ->getRepository('EvpTicketBundle:TicketType')
            ->getAllActiveAndAvailableByEvent($this->event);
        $requestedOrderDetails = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->getAllByUserAndEvent($this->user, $this->event);

        $discountCode = new DiscountCode;
        $ticketTypeCountMap = array();
        foreach ($types as $type) {
            $map = new \stdClass;
            foreach ($requestedOrderDetails as $detail) {
                if ($detail->getTicketType() == $type) {
                    $map->count = $detail->getTicketsCount();
                }
            }
            $map->ticketType = $type;
            if (empty($map->count)) {
                $map->count = 0;
            }
            $ticketsPerUser = $this->event->getEventType()->getMaxTicketsPerUser();
            $maxTickets = $ticketsPerUser > self::MAX_TICKETS_ALLOWED ? self::MAX_TICKETS_ALLOWED : $ticketsPerUser;
            $map->maxTickets = $maxTickets;

            $ticketTypeCountMap[] = $map;
        }
        $ticketsInCart = $this->entityManager->getRepository('EvpTicketBundle:Ticket')
            ->findBy(
                array(
                    'event' => $this->event,
                    'user' => $this->user,
                )
            );
        $discountsAreAvailable = false;
        if (!$this->applyDiscountStep->getIsDiscountApplied()) {
            $ticketTypes = $this->event->getTicketTypes();
            $discountsAreAvailable = false;
            foreach ($ticketTypes as $type) {
                if ($type->getDiscountTypes()->count() > 0) {
                    $discountsAreAvailable = true;
                    break;
                }
            }
        }

        if (empty($this->form)) {
            $discountCodeForm = new DiscountCodeForm($this->event, $this->user);
            $this->form = $this->formFactory->create($discountCodeForm, $discountCode);
        }

        return array(
            'ticketTypesMap' => $ticketTypeCountMap,
            'discountForm' => $this->form->createView(),
            'ticketsInCart' => $ticketsInCart,
            'discountsAreAvailable' => $discountsAreAvailable,
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return boolean
     */
    public function save(Request $request)
    {
        //Tickets already generated in AjaxController::modifyOrderDetailsAction
        return $this->applyDiscountStep->save($request);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $params
     */
    public function setParams($params)
    {
        $this->template = $params['template'];
        $this->userSession = $params['user_session'];
        $this->applyDiscountStep = $params['apply_discount_step'];
    }

    /**
     * {@inheritdoc}
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->event = $options['event'];
        $this->user = $options['user'];
        $this->applyDiscountStep->setOptions(array(
                'user' => $this->user,
                'event' => $this->event,
            ));
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
} 
