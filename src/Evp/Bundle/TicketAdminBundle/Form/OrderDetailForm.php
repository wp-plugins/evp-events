<?php
/**
 * Provides multiple TicketType selection in new Order Menu action
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Form;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class OrderDetailForm
 */
class OrderDetailForm extends AbstractType
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
     * @return FormBuilderInterface
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'ticketType',
                'entity',
                array(
                    'class' => 'EvpTicketBundle:TicketType',
                    'property' => 'name',
                    'label' => $this->translator->trans(OrderDetails::LABEL_TICKET_TYPE, array(), 'columns'),
                    'translation_domain' => 'columns',
                    'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('tt')
                                ->join('tt.event', 'e')
                                ->andWhere('e.dateEnds >= :now')
                                ->setParameter('now', new \DateTime);
                        },
                    'mapped' => false,
                    'attr' => array(
                        'name' => 'type[]',
                    )
                )
            )
            ->add(
                'ticketsCount',
                'text',
                array(
                    'label' => $this->translator->trans(OrderDetails::LABEL_TICKETS_COUNT, array(), 'columns'),
                    'data' => 1,
                    'mapped' => false,
                    'attr' => array(
                        'name' => 'count[]',
                    )
                )
            )
            ->add(
                'add',
                'button',
                array(
                    'label' => $this->translator->trans('admin.button.add', array(), 'columns')
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Evp\Bundle\TicketBundle\Entity\Step\OrderDetails'
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_order_details_ticket_type_count';
    }
}
