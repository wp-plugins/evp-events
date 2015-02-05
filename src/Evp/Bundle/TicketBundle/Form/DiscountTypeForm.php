<?php

namespace Evp\Bundle\TicketBundle\Form;

use Evp\Bundle\TicketBundle\Entity\DiscountType;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\File;

class DiscountTypeForm extends AbstractType
{
    private $strategies;
    private $types;
    private $scopes;
    private $locales;
    private $reloadUrl;
    private $currentLocale;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * Sets the parameters for Form
     *
     * @param array $params
     */
    public function setParameters($params) {
        $this->locales = $params['locales'];
        $this->translator = $params['translator'];
        $this->reloadUrl = $params['reloadUrl'];
        $this->strategies = $params['discountStrategies'];
        $this->scopes = $params['discountScopes'];
        $this->types = $params['discountTypes'];
        $this->currentLocale = $params['currentLocale'];
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
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
                'integer',
                array(
                    'attr' => array(
                        'class' => 'tickets_date_dropdowns'
                    ),
                    'label' => $this->translator->trans(DiscountType::LABEL_TICKETS_COUNT, array(), 'columns')
                )
            )
            ->add(
                'name',
                'text',
                array(
                    'label' => $this->translator->trans(DiscountType::LABEL_NAME, array(), 'columns')
                )
            )
            ->add(
                'description',
                'text',
                array(
                    'attr' => array('size' => '100px'),
                    'label' => $this->translator->trans(DiscountType::LABEL_DESCRIPTION, array(), 'columns')
                )
            )
            ->add(
                'dateStarts',
                'date',
                array(
                    'attr' => array(
                        'class' => 'tickets_date_dropdowns'
                    ),
                    'label' => $this->translator->trans(DiscountType::LABEL_DATE_STARTS, array(), 'columns')
                )
            )
            ->add(
                'dateEnds',
                'date',
                array(
                    'attr' => array(
                        'class' => 'tickets_date_dropdowns'
                    ),
                    'label' => $this->translator->trans(DiscountType::LABEL_DATE_ENDS, array(), 'columns')
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
                    'attr' => array('class' => 'tickets'),
                    'label' => $this->translator->trans(DiscountType::LABEL_STATUS, array(), 'columns')
                )
            )
            ->add('discountStrategy', 'choice', array(
                    'choices' => $this->strategies,
                    'expanded' => false,
                    'label' => $this->translator->trans(DiscountType::LABEL_DISCOUNT_STRATEGY, array(), 'columns'),

                )
            )
            ->add('type', 'choice', array(
                    'choices' => $this->types,
                    'expanded' => false,
                    'label' => $this->translator->trans(DiscountType::LABEL_DISCOUNT_TYPE, array(), 'columns'),
                )
            )
            ->add('scope', 'choice', array(
                    'choices' => $this->scopes,
                    'expanded' => false,
                    'label' => $this->translator->trans(DiscountType::LABEL_DISCOUNT_SCOPE, array(), 'columns'),
                )
            )
            ->add('multiple', 'choice', array(
                    'choices' => array(
                        0 => $this->translator->trans('admin.status.inactive', array(), 'columns'),
                        1 => $this->translator->trans('admin.status.active', array(), 'columns'),
                    ),
                    'expanded' => false,
                    'label' => $this->translator->trans(DiscountType::LABEL_DISCOUNT_TYPE_MULTIPLE, array(), 'columns'),
                )
            )
            ->add('uploaded_file', 'file', array(
                    'required' => false,
                    'label' => $this->translator->trans(DiscountType::LABEL_DISCOUNT_TYPE_UPLOAD_FILE, array(), 'columns'),
                    'constraints' => array(
                        new File(array(
                                'maxSize' => '2048k',
                                'mimeTypes' => array('text/comma-separated-values', 'text/csv', 'text/plain'),
                            ))
                    ),
                    'error_bubbling' => true,
                )
            )
            ->add('locale', 'choice', array(
                    'choices' => $this->locales,
                    'expanded' => false,
                    'data' => $this->currentLocale,
                    'attr' => $localeAttr,
                    'label' => $this->translator->trans(DiscountType::LABEL_LOCALE, array(), 'columns'),
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
            'data_class' => 'Evp\Bundle\TicketBundle\Entity\DiscountType'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_discounttype';
    }
}
