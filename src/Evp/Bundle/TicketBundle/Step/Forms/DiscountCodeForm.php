<?php
namespace Evp\Bundle\TicketBundle\Step\Forms;

use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\User;
use Evp\Bundle\TicketBundle\Step\ApplyDiscount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class DiscountCodeForm
 * @package Evp\Bundle\TicketBundle\Step\Forms
 */
class DiscountCodeForm extends AbstractType {

    const DISCOUNT_TOKEN_FIELD_NAME = 'token';
    const DISCOUNT_TOKEN_FIELD_LABEL = 'label.discount_token';

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Event
     */
    private $event;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\User
     */
    private $user;

    /**
     * @var array
     */
    private $choices = array();

    /**
     * Sets necessary Entities
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $event
     * @param \Evp\Bundle\TicketBundle\Entity\User $user
     */
    public function __construct(Event $event, User $user) {
        $this->event = $event;
        $this->user = $user;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                self::DISCOUNT_TOKEN_FIELD_NAME,
                'text',
                array(
                    'label' => self::DISCOUNT_TOKEN_FIELD_LABEL,
                    'required' => false,
                    'error_bubbling' => true,
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                )
            )
            ->add(
                ApplyDiscount::DISCOUNT_SUBMIT_BUTTON_NAME, 'submit',
                array('label' => ApplyDiscount::DISCOUNT_SUBMIT_BUTTON_LABEL)
            )
            ->getForm();
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
                'data_class' => 'Evp\Bundle\TicketBundle\Entity\Step\DiscountCode'
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_apply_discount_discount_code';
    }

    /**
     * @param array $choices
     */
    public function setChoices($choices) {
        $this->choices = $choices;
    }
}
