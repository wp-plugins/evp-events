<?php

namespace Evp\Bundle\TicketBundle\Form;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventTypeFieldSchemaForm extends AbstractType
{
    private $eventType;

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
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'fieldSchema',
                'entity',
                array(
                    'class' => 'EvpTicketBundle:Form\FieldSchema',
                    'property' => 'name',
                    'attr' => array('expanded' => 'true'),
                    'label' => $this->translator->trans(EventTypeFieldSchema::LABEL_FIELD_SCHEMA, array(), 'columns')
                )
            )

            ->add(
                'isRequired',
                'checkbox',
                array(
                    'label'     => $this->translator->trans(EventTypeFieldSchema::LABEL_REQUIRED, array(), 'columns'),
                    'required'  => false,
                )
            )
            ->add(
                'isRequiredForAll',
                'checkbox',
                array(
                    'label'     => $this->translator->trans(EventTypeFieldSchema::LABEL_REQUIRED_FOR_ALL, array(), 'columns'),
                    'required'  => false,
                )
            )
            ->add(
                'isMadeByAdmin',
                'hidden',
                array(
                    'data'     => 1,
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
            'data_class' => 'Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_EventTypeFieldSchema';
    }
}
