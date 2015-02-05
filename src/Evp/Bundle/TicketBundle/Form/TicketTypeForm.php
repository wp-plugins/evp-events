<?php

namespace Evp\Bundle\TicketBundle\Form;

use Evp\Bundle\TicketBundle\Entity\TicketType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketTypeForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */

    private $discountTypes;
    private $selectedDiscounts;
    private $reloadUrl;
    private $currentLocale;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * @var string[]
     */
    private $locales;

    /**
     * Sets the parameters for Form
     *
     * @param array $params
     */
    public function setParameters($params) {
        $this->locales = $params['locales'];
        $this->translator = $params['translator'];
        $this->reloadUrl = $params['reloadUrl'];
        $this->discountTypes = $params['discount_types'];
        $this->selectedDiscounts = $params['selected'];
        $this->currentLocale = $params['currentLocale'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $localeAttr = array();
        $localeAttr['class'] = 'tickets_date_dropdowns';
        if ($this->reloadUrl) {
            $localeAttr['onchange'] = 'refreshEntityBasedOnTargetLocale(\'' . $this->reloadUrl . '\', this.value, \'' . $this->getName() . '\')';
        }

        $builder
            ->add(
                'ticketsCount',
                'text',
                array(
                    'label' => $this->translator->trans(TicketType::LABEL_TICKETS_COUNT, array(), 'columns'),
                    'required' => false,
                )
            )
            ->add(
                'name',
                'text',
                array(
                    'required' => true,
                    'label' => $this->translator->trans(TicketType::LABEL_NAME, array(), 'columns')
                )
            )
            ->add(
                'description',
                'text',
                array(
                    'label' => $this->translator->trans(TicketType::LABEL_DESCRIPTION, array(), 'columns')
                )
            )
            ->add(
                'event',
                'entity',
                array(
                    'class' => 'EvpTicketBundle:Event',
                    'property' => 'name',
                    'label' => $this->translator->trans(TicketType::LABEL_EVENT, array(), 'columns'),
                )
            )
            ->add(
                'price',
                'text',
                array(
                    'required' => true,
                    'label' => $this->translator->trans('admin.index.entity.price', array(), 'columns')
                )
            )
            ->add(
                'discountTypesChanges',
                'choice',
                array(
                    'choices' => $this->discountTypes,
                    'multiple' => true,
                    'required' => false,
                    'expanded' => false,
                    'data' => $this->selectedDiscounts,
                    'label' => $this->translator->trans(TicketType::LABEL_DISCOUNT_TYPES, array(), 'columns')
                )
            )
            ->add('locale', 'choice',
                array(
                    'choices' => $this->locales,
                    'expanded' => false,
                    'data' => $this->currentLocale,
                    'attr' => $localeAttr,
                    'label' => $this->translator->trans(TicketType::LABEL_LOCALE, array(), 'columns')
                )
            )
            ->add(
                'status',
                'choice',
                array(
                    'choices' => array(
                        1 => $this->translator->trans('admin.status.active', array(), 'columns'),
                        0 => $this->translator->trans('admin.status.inactive', array(), 'columns')
                    ),
                    'expanded' => false,
                    'attr' => array('class' => 'tickets_date_dropdowns'),
                    'label' => $this->translator->trans('admin.status.general_label', array(), 'columns')
                )
            )
            ->add(
                'save',
                'submit',
                array(
                    'attr' => array(
                        'class' => 'tickets_btn_submit'
                    ),
                    'label' => $this->translator->trans('admin.button.save', array(), 'columns')
                )
            )
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Evp\Bundle\TicketBundle\Entity\TicketType'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_tickettype';
    }
}
