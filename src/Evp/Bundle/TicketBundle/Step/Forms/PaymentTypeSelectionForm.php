<?php
/**
 * PaymentTypeSelection Form  for multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step\Forms;

use Evp\Bundle\TicketBundle\Step\PaymentTypeSelection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class PaymentTypeSelectionForm
 */
class PaymentTypeSelectionForm extends AbstractType {

    /**
     * @var array Payment choices array
     */
    private $choices;

    /**
     * @var bool
     */
    private $invoicing;

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                PaymentTypeSelection::SELECT_PAYMENT_FIELD_NAME,
                'choice',
                array(
                    'label' => false,
                    'error_bubbling' => true,
                    'required' => true,
                    'multiple' => false,
                    'expanded' => true,
                    'choices' => $this->choices,
                )
            );
        if ($this->invoicing) {
            $builder
                ->add(
                    PaymentTypeSelection::INVOICE_REQUIRED_NAME,
                    'choice',
                    array(
                        'label' => false,
                        'error_bubbling' => true,
                        'required' => false,
                        'multiple' => true,
                        'expanded' => true,
                        'choices' => array(PaymentTypeSelection::INVOICE_REQUIRED_LABEL),
                        'attr' => array('class' => 'invoiceCheckbox'),
                    )
                );
        }
            $builder->getForm();
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_payment_type_select';
    }

    /**
     * @param array $choices
     */
    public function setChoices($choices) {
        $this->choices = $choices;
    }

    /**
     * @param bool $invoicing
     */
    public function setInvoicingEnabled($invoicing) {
        $this->invoicing = $invoicing;
    }
}
