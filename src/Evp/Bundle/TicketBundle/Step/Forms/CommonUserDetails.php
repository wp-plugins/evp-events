<?php

namespace Evp\Bundle\TicketBundle\Step\Forms;

use Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema;
use Evp\Bundle\TicketBundle\Entity\User;
use Evp\Bundle\TicketBundle\Step\UserDetailsFill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DetailsFormBuilder
 * @package Evp\Bundle\TicketBundle\Service
 * @author d.glezeris
 *
 */
class CommonUserDetails  extends AbstractType {

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema[]
     */
    protected $fieldSchemasCommon = array();

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Ticket[]
     */
    protected $tickets = array();

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        foreach ($this->fieldSchemasCommon as $field) {
            if (is_array($field)) {
                $builder->add($field['name'], 'hidden'
                );
                continue;
            }

            $fieldSchema = $field->getFieldSchema();
            $validator = $fieldSchema->getValidator();

            if (!empty($validator)) {
                $validator = new $validator;
            }

            $constraints = $field->getIsRequired() ? array($validator) : array();

            $options = array(
                'error_bubbling' => true,
                'constraints' => $constraints,
                'label' => $fieldSchema->getLabel(),
                'required' => $field->getIsRequired(),
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
     * @param array $schemas
     */
    public function setFieldSchemasCommon($schemas) {
        $this->fieldSchemasCommon = $schemas;
    }

    /**
     * @param \Evp\Bundle\TicketBundle\Entity\Ticket[] $tickets
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;
    }
}
