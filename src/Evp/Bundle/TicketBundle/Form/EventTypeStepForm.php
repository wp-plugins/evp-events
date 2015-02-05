<?php

namespace Evp\Bundle\TicketBundle\Form;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventTypeStepForm extends AbstractType
{
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
                'steps',
                'entity',
                array(
                    'class' => 'EvpTicketBundle:Step',
                    'property' => 'parameter',
                    'label' => $this->translator->trans('admin.index.entity.form_step_id', array(), 'columns'),
                    'translation_domain' => 'columns'
                )
            )
            ->add(
                'step_order', 'integer', array(
                    'label' => $this->translator->trans('admin.index.entity.step_order', array(), 'columns')
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
            'data_class' => 'Evp\Bundle\TicketBundle\Entity\EventTypeStep'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_EventTypeStep';
    }
}
