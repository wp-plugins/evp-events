<?php
/**
 * ApplyDiscount step from multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Discount;
use Evp\Bundle\TicketBundle\Entity\Step\DiscountCode;
use Evp\Bundle\TicketBundle\Entity\Step\DiscountDetails;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketBundle\Service\DiscountManager;
use Evp\Bundle\TicketBundle\Service\TicketManager;
use Evp\Bundle\TicketBundle\Service\UserSession;
use Evp\Bundle\TicketBundle\Step\Forms\ApplyDiscountForm;
use Evp\Bundle\TicketBundle\Step\Forms\DiscountCodeForm;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApplyDiscount
 * Generates a step, where user can apply various discounts
 *
 * @package Evp\Bundle\TicketBundle\Step
 */
class ApplyDiscount extends StepAbstract implements StepInterface {

    const DISCOUNT_DROPBOX_NAME = 'discountType';
    const DISCOUNT_VALUE_FIELD_NAME = 'discountValue';
    const DISCOUNT_VALUE_FIELD_LABEL = 'label.discount_value';
    const DISCOUNT_SUBMIT_BUTTON_NAME = 'submit';
    const DISCOUNT_SUBMIT_BUTTON_LABEL = 'button.submit_discount';
    const ORDER_DETAILS_ID_HIDDEN_NAME = 'orderDetailsId';
    const DISCOUNT_AVAILABLE = 'available';
    const DISCOUNT_APPLIED = 'applied';
    const KEY_REQUESTED_TICKETS = 'requestedTickets';
    const KEY_ORDER_DETAILS = 'orderDetails';

    const STEP_APPLY_DISCOUNT = 'apply_discount';

    /**
     * @var string Twig template
     */
    private $template;

    /**
     * @var \Symfony\Component\Form\AbstractType
     */
    protected $discountDetailsForm;

    /**
     * @var \Symfony\Component\Form\AbstractType
     */
    protected $discountCodeForm;

    /**
     * @var array
     */
    protected $formBody = array();

    /**
     * @var bool
     */
    protected $skipSave = false;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\DiscountManager
     */
    protected $discountManager;

    /**
     * @var
     */
    private $userSession;

    /** @var array */
    private $orderDetailsWithDiscount = array();

    /**
     * @var bool
     */
    private $isDiscountApplied = false;

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isStepCompleted()
    {
        // step not mandatory
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getStepName()
    {
        return self::STEP_APPLY_DISCOUNT;
    }

    /**
     * @param boolean $skipSave
     */
    public function setSkipSave($skipSave)
    {
        $this->skipSave = $skipSave;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $params
     */
    public function setParams($params) {
        $this->template = $params['template'];
        $this->discountManager = $params['discount_manager'];
        $this->userSession = $params['user_session'];
    }

    /**
     * Step rendering
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function render() {
        $this->formBody = array();

        $discountCode = DiscountCode::create();
        $this->formBody[self::DISCOUNT_AVAILABLE][] = $this->formFactory
            ->create($this->discountCodeForm, $discountCode)
            ->createView();

        $this->appendAlreadyAppliedDiscounts();

        $requestedOrderDetails = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->getAllByUserAndEvent($this->user, $this->event);
        if (!empty($requestedOrderDetails)) {
            $this->formBody[self::KEY_ORDER_DETAILS] = $requestedOrderDetails;
        }
        $requestedTickets = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->findBy(
                array(
                    'user' => $this->user,
                    'event' => $this->event,
                )
            );
        $this->formBody[self::KEY_REQUESTED_TICKETS] = $requestedTickets;

        return $this->formBody;
    }

    /**
     * Step validation
     * @param \Symfony\Component\HttpFoundation\Request
     * @return boolean|array
     */
    function validate(Request $request) {
        $discountCode = DiscountCode::create();
        $form = $this->formFactory->create($this->discountCodeForm, $discountCode);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $this->skipSave = true;
            return null;
        }

        $orderDetailsRepo = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails');
        $discountTypeRepo = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\DiscountType');

        $user = $this->userSession->getUserForThisSession();
        $orderDetails = $orderDetailsRepo->getNotDiscountedByUserAndEvent(
            $user,
            $user->getOrder()->getEvent()
        );

        foreach ($orderDetails as $orderDetail) {
            $discountType = $discountTypeRepo->getOneByTicketTypeAndToken($orderDetail->getTicketType(), $discountCode->getToken());

            if (empty($discountType)) {
                continue;
            }

            $this->discountManager->setDependentEntities($this->event, $this->user, $orderDetail->getTicketType());

            $ticketsAvailableAmount = $this->discountManager->validateCode(
                $discountCode->getToken(),
                $discountType
            );

            if ($ticketsAvailableAmount > 0) {
                $this->orderDetailsWithDiscount[$orderDetail->getId()] = array(
                    'orderDetail'            => $orderDetail,
                    'validatedDiscount'      => $this->discountManager->getValidatedDiscount(),
                    'ticketsAvailableAmount' => $ticketsAvailableAmount,
                );
            }
        }

        if (empty($ticketsAvailableAmount)) {
            $errors = array('error.check.invalid_code');
            return $errors;
        }
    }

    /**
     * Saving step data
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    function save(Request $request) {
        if ($this->skipSave) {
            return true;
        }

        foreach ($this->orderDetailsWithDiscount as $orderDetailId => $detailsWithDiscount) {

            /** @var Discount $validatedDiscount */
            $validatedDiscount = $detailsWithDiscount['validatedDiscount'];
            $orderDetail = $detailsWithDiscount['orderDetail'];
            $ticketsAvailableAmount = $detailsWithDiscount['ticketsAvailableAmount'];

            /** @var Ticket[] $ticketsToApply */
            $ticketsToApply = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
                ->getNotDiscountedByEventAndUserAndOrderDetails(
                    $this->event,
                    $this->user,
                    $orderDetail
                );

            $appliedTickets = array();

            if ($ticketsAvailableAmount >= count($ticketsToApply)) {
                foreach ($ticketsToApply as $ticket) {
                    $ticket->setDiscount($validatedDiscount);
                    $this->markDiscount($validatedDiscount);
                    $appliedTickets[] = $ticket;
                    $this->entityManager->persist($ticket);
                }
            } else {
                for ($i = 0; $i < $ticketsAvailableAmount; $i++) {
                    $ticketsToApply[$i]->setDiscount($validatedDiscount);
                    $this->markDiscount($validatedDiscount);
                    $appliedTickets[] = $ticketsToApply[$i];
                    $this->entityManager->persist($ticketsToApply[$i]);
                }
            }
            $this->isDiscountApplied = $this->discountManager->applyDiscount($appliedTickets);
        }
        $this->entityManager->flush();

        return false;
    }

    /**
     * @param Discount $discount
     */
    private function markDiscount(Discount $discount)
    {
        $isMultiple = $discount->getDiscountType()->getMultiple();
        if ($isMultiple) {
            $discount->setStatus(Discount::STATUS_AVAILABLE);
        } else {
            $discount->setStatus(Discount::STATUS_USED);
        }
        $this->entityManager->persist($discount);
    }

    /**
     * @param array $options
     * @return $this
     */
    function setOptions($options) {
        $this->event = $options['event'];
        $this->user = $options['user'];
        $this->discountDetailsForm = new ApplyDiscountForm($this->event, $this->user);
        $this->discountCodeForm = new DiscountCodeForm($this->event, $this->user);
        return $this;
    }

    /**
     * Gets template form
     * @return string
     */
    function getTemplate() {
        return $this->template;
    }


    /**
     * Appends a form with already applied discounts that cannot be changed
     */
    private function appendAlreadyAppliedDiscounts()
    {
        $discountedOrderDetails = $this->entityManager->getRepository(
            'Evp\Bundle\TicketBundle\Entity\Step\OrderDetails'
        )
            ->getDiscountedByUserAndEvent($this->user, $this->event);

        if (!empty($discountedOrderDetails)) {
            foreach ($discountedOrderDetails as $orderDetail) {
                $discountDetails = new DiscountDetails;
                $ticket = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
                    ->getOneByUserAndEventAndOrderDetail($this->user, $this->event, $orderDetail);

                $discountDetails->setTicketType($orderDetail->getTicketType());
                $discountDetails->setTicket($ticket);
                $this->formBody[self::DISCOUNT_APPLIED][] = $discountDetails;
            }
        }
    }

    /**
     * @return boolean
     */
    public function getIsDiscountApplied()
    {
        return $this->isDiscountApplied;
    }
}
