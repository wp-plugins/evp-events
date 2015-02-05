<?php
/**
 * ApplyDiscount Form  for multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step\Forms;

use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\User;
use Evp\Bundle\TicketBundle\Step\ApplyDiscount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ApplyDiscountForm
 */
class ApplyDiscountForm extends AbstractType {
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
                ApplyDiscount::DISCOUNT_DROPBOX_NAME,
                'choice',
                array(
                    'label' => false,
                    'error_bubbling' => true,
                    'required' => false,
                    'empty_value' => 'choose.discount_type',
                    'empty_data' => null,
                    'choices' => $this->choices,
                )
            )
            ->add(
                ApplyDiscount::DISCOUNT_VALUE_FIELD_NAME,
                'text',
                array(
                    'label' => ApplyDiscount::DISCOUNT_VALUE_FIELD_LABEL,
                    'required' => false,
                )
            )
            ->add(ApplyDiscount::ORDER_DETAILS_ID_HIDDEN_NAME, 'hidden')
            ->add(
                ApplyDiscount::DISCOUNT_SUBMIT_BUTTON_NAME, 'submit',
                array('label' => ApplyDiscount::DISCOUNT_SUBMIT_BUTTON_LABEL,)
            )
            ->getForm();
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
                'data_class' => 'Evp\Bundle\TicketBundle\Entity\Step\DiscountDetails'
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_apply_discount';
    }

    /**
     * @param array $choices
     */
    public function setChoices($choices) {
        $this->choices = $choices;
    }
}
