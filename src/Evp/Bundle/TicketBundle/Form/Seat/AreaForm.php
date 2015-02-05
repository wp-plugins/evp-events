<?php

namespace Evp\Bundle\TicketBundle\Form\Seat;

use Evp\Bundle\TicketBundle\Entity\Seat\Area;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AreaForm extends AbstractType
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * @var bool
     */
    private $isParent;

    /**
     * Sets the parameters for Form
     *
     * @param array $params
     */
    public function setParameters($params) {
        $this->translator = $params['translator'];
        $this->isParent = $params['is_parent'];
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'columns',
                'text',
                array(
                    'label' => $this->translator->trans(Area::LABEL_COLUMN, array(), 'columns'),
                    'disabled' => $this->isParent,
                )
            )
            ->add(
                'rows',
                'text',
                array(
                    'label' => $this->translator->trans(Area::LABEL_ROW, array(), 'columns'),
                )
            )
            ->add(
                'shapeTemplate',
                'text',
                array(
                    'label' => $this->translator->trans(Area::LABEL_SHAPE_TEMPLATE, array(), 'columns'),
                    'disabled' => $this->isParent,
                )
            )
            ->add(
                'shapeOffsetX',
                'text',
                array(
                    'label' => $this->translator->trans(Area::LABEL_SHAPE_OFFSET_X, array(), 'columns'),
                    'disabled' => $this->isParent,
                )
            )
            ->add(
                'shapeOffsetY',
                'text',
                array(
                    'label' => $this->translator->trans(Area::LABEL_SHAPE_OFFSET_Y, array(), 'columns'),
                    'disabled' => $this->isParent,
                )
            )
            ->add(
                'shapeFillColor',
                'text',
                array(
                    'label' => $this->translator->trans(Area::LABEL_SHAPE_FILL_COLOR, array(), 'columns'),
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
            'data_class' => 'Evp\Bundle\TicketBundle\Entity\Seat\Area'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_seat_area';
    }
}
