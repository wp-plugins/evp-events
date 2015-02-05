<?php
/**
 * PaymentTypeSelection step from multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\PaymentBundle\Entity\PaymentType;
use Evp\Bundle\PaymentBundle\Service\PaymentHandlerProvider;
use Evp\Bundle\TicketBundle\Entity\Step\PaymentChoice;
use Evp\Bundle\TicketBundle\Service\OrderManager;
use Evp\Bundle\TicketBundle\Service\TicketManager;
use Evp\Bundle\TicketBundle\Service\UserSession;
use Evp\Bundle\TicketBundle\Step\Forms\PaymentTypeSelectionForm;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PaymentTypeSelection
 */
class PaymentTypeSelection extends StepAbstract implements StepInterface {

    const SELECT_PAYMENT_FIELD_NAME = 'paymentChoice';
    const SUBMIT_BUTTON_NAME = 'submit_payment_type';
    const INVOICE_TYPE_NAME = 'invoice';
    const INVOICE_TYPE_LABEL = 'label.payment.invoice';
    const INVOICE_REQUIRED_NAME = 'invoiceRequired';
    const INVOICE_REQUIRED_LABEL = 'label.payment.invoice_required';

    const STEP_PAYMENT_TYPE_SELECTION = 'payment_type_selection';

    /**
     * @var string Twig template
     */
    private $template;

    /**
     * @var \Evp\Bundle\PaymentBundle\Service\PaymentHandlerProvider
     */
    private $paymentHandler;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\OrderManager
     */
    private $orderManager;

    /**
     * @var \Symfony\Component\Form\AbstractType
     */
    protected $form;

    /**
     * @var string
     */
    private $paymentChoice = null;

    /**
     * @var bool
     */
    private $invoiceNeeded = false;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\UserSession
     */
    private $userSession;

    /**
     * {@inheritdoc}
     *
     * @param array $params
     */
    public function setParams($params) {
        $this->template = $params['template'];
        $this->paymentHandler = $params['payment_handler'];
        $this->orderManager = $params['order_manager'];
        $this->userSession = $params['user_session'];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getStepName()
    {
        return self::STEP_PAYMENT_TYPE_SELECTION;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isStepCompleted()
    {
        $paymentType = $this->user->getOrder()->getPaymentType();
        return !empty($paymentType);
    }

    /**
     * Step validation
     * @param \Symfony\Component\HttpFoundation\Request
     * @return boolean|array
     */
    public function validate(Request $request) {
        $form = $this->formFactory->create($this->form);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return false;
        }
        $requestArray = $request->request->get($form->getName());
        $this->paymentChoice = $requestArray[self::SELECT_PAYMENT_FIELD_NAME];
        $this->invoiceNeeded = array_key_exists(self::INVOICE_REQUIRED_NAME, $requestArray);

        if (!empty($this->paymentChoice)) {
            return null;
        }
        return true;
    }

    /**
     * Step rendering
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render() {
        $currentUser = $this->userSession->getUserForThisSession();

        $paymentHandlers = $this->paymentHandler->getAllTaggedServices();
        $allPaymentTypes = array();
        foreach ($paymentHandlers as $paymentHandler) {
            $paymentTypes = $paymentHandler->getPaymentTypesForUser($currentUser);
            $allPaymentTypes = array_merge($allPaymentTypes, $paymentTypes);
        }

        $this->form->setChoices($this->getChoicesFromPaymentTypesArray($allPaymentTypes));
        $this->form->setInvoicingEnabled($this->event->getEventType()->getInvoicingEnabled());
        return $this->formFactory->create($this->form)->createView();
    }

    /**
     * Saving step data
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    public function save(Request $request) {
        $order = $this->user->getOrder();
        $order->setPaymentType($this->paymentChoice);
        $order->setInvoiceRequired($this->invoiceNeeded);
        $this->entityManager->flush($order);
        return true;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions($options) {
        $this->event = $options['event'];
        $this->user = $options['user'];
        $this->form = new PaymentTypeSelectionForm;
        $this->orderManager->updateTicketCountAndPrice($this->user->getOrder());
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
     * Creates new PaymentType Entity
     *
     * @param string $type
     * @return \Evp\Bundle\PaymentBundle\Entity\PaymentType
     */
    private function createPaymentTypeEntity($type = self::INVOICE_TYPE_NAME) {
        $entity = new PaymentType;
        if ($type == self::INVOICE_TYPE_NAME) {
            $entity->setName(self::INVOICE_TYPE_NAME);
            $entity->setTitle(self::INVOICE_TYPE_LABEL);
        }
        return $entity;
    }

    /**
     * Gets array for key-value choices for FormBuilder
     *
     * @param \Evp\Bundle\PaymentBundle\Entity\PaymentType[] $paymentTypes
     * @return array
     */
    private function getChoicesFromPaymentTypesArray($paymentTypes) {
        $choices = array();
        foreach ($paymentTypes as $paymentType) {
            $choices[$paymentType->getName()] = $paymentType->getTitle();
        }
        return $choices;
    }
}
