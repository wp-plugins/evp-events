<?php
/**
 * InvoiceDetailsFill Form  for multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step\Forms;

use Evp\Bundle\TicketBundle\Step\PaymentWindow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class InvoiceDetailsFillForm
 */
class InvoiceDetailsFillForm extends AbstractType {

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
/*            ->add('type', 'choice', array(
                    'required' => true,
                    'error_bubbling' => true,
                    'label' => PaymentWindow::INVOICE_STATUS_LABEL,
                    'expanded' => true,
                    'data' => 'person',
                    'choices' =>array(
                        'person' => PaymentWindow::INVOICE_PERSON_LABEL,
                        'legal' => PaymentWindow::INVOICE_LEGAL_LABEL,
                    )
                )
            )*/
            ->add('name', 'text', array(
                    'required' => true,
                    'error_bubbling' => true,
                    'label' => PaymentWindow::INVOICE_NAME_LABEL,
                    'constraints' => array(
                        new Assert\Length(array(
                            'min' => 5,
                            'minMessage' => 'value.invoice.name.too_short',
                            'max' => 50,
                            'maxMessage' => 'value.invoice.name.too_long',
                        )),
                        new Assert\NotBlank,
                    )
                )
            )
            ->add('code', 'text', array(
                    'required' => true,
                    'error_bubbling' => true,
                    'label' => PaymentWindow::INVOICE_CODE_LABEL,
                    'constraints' => array(
                        new Assert\Length(array(
                            'min' => 5,
                            'minMessage' => 'value.invoice.code.too_short',
                            'max' => 25,
                            'maxMessage' => 'value.invoice.code.too_long',
                        )),
                        new Assert\Regex(array(
                            'pattern' => '/^[0-9]+\S$/',
                            'message' => 'value.invoice.code.pattern_mismatch'
                        )),
                        new Assert\NotBlank,
                    )
                )
            )
            ->add('address', 'text', array(
                    'required' => false,
                    'error_bubbling' => true,
                    'label' => PaymentWindow::INVOICE_ADDRESS_LABEL,
                    'constraints' => array(
                        new Assert\Length(array(
                            'min' => 5,
                            'minMessage' => 'value.invoice.address.too_short',
                            'max' => 50,
                            'maxMessage' => 'value.invoice.address.too_long',
                        )),
                    )
                )
            )
            ->add('vatCode', 'text', array(
                    'required' => false,
                    'error_bubbling' => true,
                    'label' => PaymentWindow::INVOICE_VAT_CODE_LABEL,
                    'constraints' => array(
                        new Assert\Length(array(
                            'min' => 5,
                            'minMessage' => 'value.invoice.vat_code.too_short',
                            'max' => 25,
                            'maxMessage' => 'value.invoice.vat_code.too_long',
                        )),
                    )
                )
            )
            ->getForm();
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
        return 'evp_bundle_ticketbundle_invoice_details_fill';
    }
} 
