<?php

namespace Evp\Bundle\TicketBundle\Form;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Form\FieldSchema;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldSchemaForm extends AbstractType
{
    private $fieldTypes;
    private $locales;
    private $reloadUrl_validators;
    private $reloadUrl_locales;
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
        $this->translator = $params['translator'];
        $this->fieldTypes = $params['fieldTypes'];
        $this->locales = $params['locales'];
        $this->reloadUrl_validators = $params['reloadUrl_validators'];
        $this->reloadUrl_locales = $params['reloadUrl_locales'];
        $this->currentLocale = $params['currentLocale'];
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                array(
                    'label' => $this->translator->trans(FieldSchema::LABEL_NAME, array(), 'columns')
                )
            )
            ->add(
                'type',
                'choice',
                array(
                    'choices' => $this->fieldTypes,
                    'expanded' => false,
                    'empty_value' => ' - - - ',
                    'attr' => array(
                        'onchange' => 'loadServiceResponse(\'' . $this->reloadUrl_validators . '\', this.value, \'validator\', \'' . $this->getName() . '_validator' . '\')',
                        'label' => $this->translator->trans(FieldSchema::LABEL_TYPE, array(), 'columns')
                        )
                )
            )
            ->add(
                'validator',
                'text',
                array (
                    'attr' => array(
                        'readonly' => true,
                        'size' => 50
                    ),
                    'label' => $this->translator->trans(FieldSchema::LABEL_VALIDATOR, array(), 'columns')
                )
            )
            /*
            ->add('validator', 'choice', array(
                    'choices' => $this->validators,
                    'expanded' => false,
                    'attr' => array('class' => 'tickets_date_dropdowns')
                )
            )
            */
            ->add(
                'field_order',
                'text',
                array(
                    'label' => $this->translator->trans(FieldSchema::LABEL_FIELD_ORDER, array(), 'columns')
                )
            )
            ->add('locale', 'choice',
                array(
                    'choices' => $this->locales,
                    'expanded' => false,
                    'data' => $this->currentLocale,
                    'attr' => array(
                        'class' => 'tickets_date_dropdowns',
                        'onchange' => 'refreshEntityBasedOnTargetLocale(\'' . $this->reloadUrl_locales . '\', this.value, \'' . $this->getName() . '\')',
                        'label' => $this->translator->trans(FieldSchema::LABEL_LOCALE, array(), 'columns')
                    )
                )
            )
            ->add(
                'label',
                'text',
                array(
                    'label' => $this->translator->trans(FieldSchema::LABEL_LABEL, array(), 'columns')
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
            'data_class' => 'Evp\Bundle\TicketBundle\Entity\Form\FieldSchema'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_field_schema';
    }
}
