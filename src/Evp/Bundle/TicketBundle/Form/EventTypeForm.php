<?php

namespace Evp\Bundle\TicketBundle\Form;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Evp\Bundle\TicketBundle\Entity\EventType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventTypeForm extends AbstractType
{
    /**
     * @var string[]
     */
    private $locales;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    private $reloadUrl;

    private $currentLocale;

    /**
     * Sets the parameters for Form
     *
     * @param array $params
     */
    public function setParameters($params) {
        $this->locales = $params['locales'];
        $this->translator = $params['translator'];
        $this->reloadUrl = $params['reloadUrl'];
        $this->currentLocale = $params['currentLocale'];
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', 'choice',
                array(
                    'choices' => $this->locales,
                    'data' => $this->currentLocale,
                    'expanded' => false,
                    'attr' => array(
                            'class' => 'tickets_date_dropdowns',
                            'onchange' => 'refreshEntityBasedOnTargetLocale(\'' . $this->reloadUrl . '\', this.value, \'' . $this->getName() . '\')',
                            'label' => $this->translator->trans(EventType::LABEL_LOCALE, array(), 'columns')
                    )
                )
            )
            ->add(
                'name',
                'text',
                array(
                    'label' => $this->translator->trans(EventType::LABEL_NAME, array(), 'columns')
                )
            )
            ->add(
                'maxTicketsPerUser',
                'text',
                array(
                    'label' => $this->translator->trans(EventType::LABEL_MAX_TICKETS_PER_USER, array(), 'columns')
                )
            )

            ->add('status', 'choice', array(
                'choices' => array(
                    1 => $this->translator->trans('admin.status.active', array(), 'columns'),
                    0 => $this->translator->trans('admin.status.inactive', array(), 'columns'),
                ),
                'expanded' => false,
                'attr' => array('class' => 'tickets'),
                'label' => $this->translator->trans(EventType::LABEL_STATUS, array(), 'columns')
            ))

            ->add('payByInvoice', 'choice', array(
                'choices' => array(
                    1 => $this->translator->trans('admin.status.active', array(), 'columns'),
                    0 => $this->translator->trans('admin.status.inactive', array(), 'columns'),
                ),
                'expanded' => false,
                'attr' => array('class' => 'tickets'),
                'label' => $this->translator->trans(EventType::LABEL_PAY_BY_INVOICE_ENABLED, array(), 'columns'),
            ))

            ->add('invoicingEnabled', 'choice', array(
                'choices' => array(
                    1 => $this->translator->trans('admin.status.active', array(), 'columns'),
                    0 => $this->translator->trans('admin.status.inactive', array(), 'columns'),
                ),
                'expanded' => false,
                'attr' => array('class' => 'tickets'),
                'label' => $this->translator->trans(EventType::LABEL_INVOICING_ENABLED, array(), 'columns'),
            ))

            ->add(
                'save',
                'submit',
                array(
                    'attr' => array(
                        'class' => 'tickets_btn_submit',
                    ),
                    'label' => $this->translator->trans('admin.button.save', array(), 'columns')
                )
            );
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Evp\Bundle\TicketBundle\Entity\EventType'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_eventtype';
    }
}
