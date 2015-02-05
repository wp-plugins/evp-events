<?php

namespace Evp\Bundle\TicketBundle\Form;

use Evp\Bundle\TicketBundle\Entity\Discount;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DiscountForm extends AbstractType
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
                'discountType',
                'entity',
                array(
                    'class' => 'EvpTicketBundle:DiscountType',
                    'property' => 'name',
                    'label' => $this->translator->trans(Discount::LABEL_DISCOUNT_TYPE, array(), 'columns')
                )
            )
            ->add(
                'token',
                'text',
                array(
                    'label' => $this->translator->trans(Discount::LABEL_TOKEN, array(), 'columns')
                )
            )
            ->add(
                'value',
                'text',
                array(
                    'label' => $this->translator->trans(Discount::LABEL_VALUE, array(), 'columns')
                )
            )
            ->add(
                'name',
                'text',
                array(
                    'label' => $this->translator->trans(Discount::LABEL_NAME, array(), 'columns')
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
            'data_class' => 'Evp\Bundle\TicketBundle\Entity\Discount'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_discount';
    }
}
