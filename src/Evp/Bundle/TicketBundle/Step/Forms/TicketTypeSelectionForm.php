<?php
/**
 * TicketTypeSelection Form  for multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Step\Forms;

use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Evp\Bundle\TicketBundle\Entity\User;
use Evp\Bundle\TicketBundle\Step\TicketTypeSelection;
use Evp\Bundle\TicketBundle\Validator\Constraints\AvailableToBuyCount;
use Evp\Bundle\TicketBundle\Validator\Constraints\MaxTicketsPerUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketTypeSelectionForm
 */
class TicketTypeSelectionForm extends AbstractType
{
    const MAX_TICKETS_ALLOWED = 100;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Event
     */
    private $event;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\User
     */
    private $user;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\TicketType
     */
    private $ticketType;

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
        $choices = array();

        $ticketsPerUser = $this->event->getEventType()->getMaxTicketsPerUser();
        $maxTickets = $ticketsPerUser > self::MAX_TICKETS_ALLOWED ? self::MAX_TICKETS_ALLOWED : $ticketsPerUser;

        for($i = 1; $i <= $maxTickets; $i++) {
            $choices[$i] = $i;
        }

        $builder
            ->add(
                TicketTypeSelection::TICKET_COUNT_FIELD_NAME,
                'choice',
                array(
                    'choices' => $choices,
                    'label' => false,
                    'error_bubbling' => true,
                    'constraints' => array(
                        new MaxTicketsPerUser(array(
                            'event' => $this->event,
                            'user' => $this->user,
                            'message' => 'max.ticket.limit_per_user',
                        )),
                        new Assert\GreaterThan(array(
                            'value' => 0,
                            'message' => 'value.greater.than',
                        )),
                        new AvailableToBuyCount(array(
                            'event' => $this->event,
                            'ticketType' => $this->ticketType,
                            'message' => 'available.to.buy.count',
                        )),
                    ),
                )
            )
            ->add(
                TicketTypeSelection::ADD_TO_CART_BUTTON_NAME, 'submit',
                array('label' => TicketTypeSelection::ADD_TO_CART_BUTTON_LABEL,)
            )
            ->add(TicketTypeSelection::TICKET_TYPE_ID_HIDDEN_NAME, 'hidden')
            ->getForm();
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
                'data_class' => 'Evp\Bundle\TicketBundle\Entity\Step\OrderDetails'
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_ticket_type_selection';
    }

    /**
     * @param \Evp\Bundle\TicketBundle\Entity\TicketType $ticketType
     */
    public function setTicketType(TicketType $ticketType) {
        $this->ticketType = $ticketType;
    }
}
