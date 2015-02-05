<?php
/**
 * PaymentWindow step from multi-step forms
 * It displays also InvoiceDetailsFill Form if User requested to receive invoice after payment
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Step\InvoiceDetails;
use Evp\Bundle\TicketBundle\Service\PaymentManager;
use Evp\Bundle\TicketBundle\Service\TicketManager;
use Evp\Bundle\TicketBundle\Step\Forms\InvoiceDetailsFillForm;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class PaymentWindow
 */
class PaymentWindow extends StepAbstract implements StepInterface {

    const INVOICE_NAME_LABEL = 'label.invoice.name';
    const INVOICE_CODE_LABEL = 'label.invoice.code';
    const INVOICE_ADDRESS_LABEL = 'label.invoice.address';
    const INVOICE_VAT_CODE_LABEL = 'label.invoice.code_vat';
    const INVOICE_LEGAL_LABEL = 'label.invoice.legal_type';
    const INVOICE_PERSON_LABEL = 'label.invoice.person_type';
    const INVOICE_STATUS_LABEL = 'label.invoice.type_label';

    const PAYMENT_REDIRECT = 'EvpPaymentBundle:Payment:redirectToPayment';

    const STEP_PAYMENT_WINDOW = 'payment_window';

    /**
     * @var string twig template
     */
    private $template;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\PaymentManager
     */
    private $paymentManager;

    /**
     * @var \Symfony\Component\Form\AbstractType
     */
    protected $form;

    /**
     * @var bool
     */
    private $invoiceRequired = false;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Step\InvoiceDetails
     */
    private $invoiceDetails;

    /**
     * {@inheritdoc}
     *
     * @param array $params
     */
    public function setParams($params) {
        $this->template = $params['template'];
        $this->paymentManager = $params['payment_manager'];
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isStepCompleted()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getStepName()
    {
        return self::STEP_PAYMENT_WINDOW;
    }

    /**
     * Step validation
     * @param \Symfony\Component\HttpFoundation\Request
     * @return boolean|array
     */
    public function validate(Request $request) {
        $this->invoiceRequired = $this->paymentManager->isInvoiceRequired($this->user);
        if (!$this->invoiceRequired) {
            return null;
        }
        $invoiceDetails = new InvoiceDetails;
        $form = $this->formFactory->create($this->form, $invoiceDetails);
        $form->handleRequest($request);
        $this->invoiceDetails = $invoiceDetails;

        $errors = array();
        if (!$form->isValid()) {
            foreach ($form->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            return array_unique($errors);
        }
        return null;
    }

    /**
     * Step rendering
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render() {
        $this->invoiceRequired = $this->paymentManager->isInvoiceRequired($this->user);
        if (!$this->invoiceRequired) {
            return null;
        }
        return $this->formFactory->create($this->form, $this->invoiceDetails)->createView();
    }

    /**
     * Saving step data
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    public function save(Request $request) {
        if (!$this->invoiceRequired) {
            return true;
        }
        $this->invoiceDetails->setDateCreated(new \DateTime);
        $order = $this->user->getOrder();
        $this->invoiceDetails->setOrder($order);
        $this->invoiceDetails->setUser($this->user);
        $this->entityManager->persist($this->invoiceDetails);
        $this->entityManager->flush();

        $order->setInvoice($this->invoiceDetails);
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
        $this->form = new InvoiceDetailsFillForm;
        return $this;
    }

    /**
     * Gets template form
     * @return string
     */
    public function getTemplate() {
        return $this->template;
    }
}
