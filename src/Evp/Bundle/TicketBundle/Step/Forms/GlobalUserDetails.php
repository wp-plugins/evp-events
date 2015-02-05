<?php

namespace Evp\Bundle\TicketBundle\Step\Forms;

use Evp\Bundle\TicketBundle\Entity\User;
use Evp\Bundle\TicketBundle\Step\UserDetailsFill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class GlobalUserDetails
 * @package Evp\Bundle\TicketBundle\Step\Forms
 * @author d.glezeris
 */
class GlobalUserDetails  extends AbstractType {

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema[]
     */
    protected $fieldSchemasGlobal = array();

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        foreach ($this->fieldSchemasGlobal as $eventTypeFieldSchema) {
            $fieldSchema = $eventTypeFieldSchema->getFieldSchema();
            $validator = $fieldSchema->getValidator();

            if (!empty($validator)) {
                $validator = new $validator;
            }

            $constraints = $eventTypeFieldSchema->getIsRequired() ? array($validator) : array();

            $options = array(
                'error_bubbling' => true,
                'constraints' => $constraints,
                'label' => $fieldSchema->getLabel(),
                'required' => $eventTypeFieldSchema->getIsRequired(),
            );

            $builder->add($fieldSchema->getName(), $fieldSchema->getType(), $options);
        }

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
                'data_class' => 'Evp\Bundle\TicketBundle\Entity\Dynamic\Entity',
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_user_details_fill';
    }

    /**
     * @param array $fieldSchemasForEveryone
     */
    public function setFieldSchemasGlobal($fieldSchemasForEveryone) {
        $this->fieldSchemasGlobal = $fieldSchemasForEveryone;
    }
}
