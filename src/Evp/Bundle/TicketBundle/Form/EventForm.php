<?php

namespace Evp\Bundle\TicketBundle\Form;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Evp\Bundle\TicketBundle\Entity\Event;

class EventForm extends AbstractType
{
    private $locales;
    private $currencies;
    private $reloadUrl;
    private $currentLocale;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * @var string[]
     */
    private $countryCodes;

    /**
     * @var string
     */
    private $currentCountryCode;

    /**
     * Sets the parameters for Form
     *
     * @param array $params
     */
    public function setParameters($params) {
        $this->locales = $params['locales'];
        $this->currencies = $params['currencies'];
        $this->translator = $params['translator'];
        $this->reloadUrl = $params['reloadUrl'];
        $this->currentLocale = $params['currentLocale'];
        $this->countryCodes = $params['countryCodes'];
        $this->currentCountryCode = $params['currentCountryCode'];
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'EventType',
                'entity',
                    array(
                        'class' => 'EvpTicketBundle:EventType',
                        'property' => 'name',
                        'label' => $this->translator->trans(Event::LABEL_EVENT_TYPE, array(), 'columns')
                    )
            )
            ->add(
                'name',
                'text',
                array(
                    'label' => $this->translator->trans(Event::LABEL_NAME, array(), 'columns')
                )
            )
            ->add(
                'description',
                'ckeditor',
                array(
                    'config_name' => 'body_content',
                    'attr' => array(
                        'class' => 'ckeditor'
                    ),
                    'label' => $this->translator->trans(Event::LABEL_DESCRIPTION, array(), 'columns')
                )
            )
            ->add(
                'currency',
                'choice',
                array(
                    'choices' => $this->currencies,
                    'expanded' => false,
                    'attr' => array('class' => 'tickets_date_dropdowns'),
                    'label' => $this->translator->trans(Event::LABEL_CURRENCY, array(), 'columns')
                )
            )
            ->add(
                'defaultLocale',
                'choice',
                array(
                    'label'  => $this->translator->trans(Event::LABEL_DEFAULT_LOCALE, array(), 'columns'),
                    'choices' => $this->locales,
                    'expanded' => false,
                    'attr' => array('class' => 'tickets_date_dropdowns'),
                )
            )
            ->add('locale', 'choice',
                array(
                    'choices' => $this->locales,
                    'expanded' => false,
                    'data' => $this->currentLocale,
                    'attr' => array(
                                'class' => 'tickets_date_dropdowns',
                                'onchange' => 'refreshEntityBasedOnTargetLocale(\'' . $this->reloadUrl . '\', this.value, \'' . $this->getName() . '\')',
                                'label' => $this->translator->trans(Event::LABEL_LOCALE, array(), 'columns')
                                )
                )
            )
            ->add(
                'countryCode',
                'choice',
                array(
                    'choices' => $this->countryCodes,
                    'expanded' => false,
                    'data' => $this->currentCountryCode,
                    'attr' => array(
                        'class' => 'tickets_date_dropdowns',
                        'onchange' => 'refreshEntityBasedOnTargetLocale(\'' . $this->reloadUrl . '\', this.value, \'' . $this->getName() . '\')',
                        'label' => $this->translator->trans(Event::LABEL_COUNTRY_CODE, array(), 'columns')
                    )
                )
            )
            ->add(
                'dateOnSale',
                'datetime',
                array(
                    'attr' => array(
                        'class' => 'tickets_date_dropdowns'
                    ),
                    'label' => $this->translator->trans(Event::LABEL_DATE_ON_SALE, array(), 'columns')
                )
            )
            ->add(
                'dateStarts',
                'datetime',
                array(
                    'attr' => array(
                        'class' => 'tickets_date_dropdowns'
                    ),
                    'label' => $this->translator->trans(Event::LABEL_DATE_STARTS, array(), 'columns')
                )
            )
            ->add(
                'dateEnds',
                'datetime',
                array(
                    'attr' => array(
                        'class' => 'tickets_date_dropdowns'
                    ),
                    'label' => $this->translator->trans(Event::LABEL_DATE_ENDS, array(), 'columns')
                )
            )
            ->add(
                'enabled',
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
                'globalEntityTemplate',
                'choice',
                array(
                    'choices' => array(
                        1 => $this->translator->trans('admin.status.active', array(), 'columns'),
                        0 => $this->translator->trans('admin.status.inactive', array(), 'columns')
                    ),
                    'expanded' => false,
                    'attr' => array('class' => 'tickets_date_dropdowns'),
                    'label' => $this->translator->trans(Event::LABEL_GLOBAL_TEMPLATE_ENTITY, array(), 'columns')
                )
            )
            ->add(
                'breadcrumbsEnabled',
                'choice',
                array(
                    'choices' => array(
                        1 => $this->translator->trans('admin.status.active', array(), 'columns'),
                        0 => $this->translator->trans('admin.status.inactive', array(), 'columns')
                    ),
                    'expanded' => false,
                    'attr' => array('class' => 'tickets_date_dropdowns'),
                    'label' => $this->translator->trans(Event::LABEL_BREADCRUMBS_ENABLED, array(), 'columns')
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
        );
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Evp\Bundle\TicketBundle\Entity\Event'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_event';
    }
}
