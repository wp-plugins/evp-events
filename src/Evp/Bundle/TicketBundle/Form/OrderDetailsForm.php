<?php

namespace Evp\Bundle\TicketBundle\Form;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketAdminBundle\Form\OrderDetailForm;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\Translator;

class OrderDetailsForm extends AbstractType
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * @var string
     */
    private $reloadUrl;

    /**
     * @var \Evp\Bundle\TicketAdminBundle\Form\OrderDetailForm
     */
    private $ticketTypeCountForm;

    /**
     * @var string
     */
    private $emptyText;

    /**
     * @var bool
     */
    private $disableSubmit;

    /**
     * Sets the parameters for Form
     *
     * @param array $params
     */
    public function setParameters($params) {
        $this->translator = $params['translator'];
        $this->reloadUrl = $params['reloadUrl'];
        $this->emptyText = $params['emptyText'];
        $this->disableSubmit = $params['disableSubmit'];

        $this->ticketTypeCountForm = new OrderDetailForm;
        $this->ticketTypeCountForm->setParameters($params);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'event',
                'entity',
                array(
                    'class' => 'EvpTicketBundle:Event',
                    'property' => 'name',
                    'empty_value' => $this->emptyText,
                    'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('e')
                                ->where('e.dateEnds >= :now')
                                ->setParameter('now', new \DateTime);
                        },
                    'label' => $this->translator->trans(OrderDetails::LABEL_EVENT, array(), 'columns'),
                    'translation_domain' => 'columns',
                    'attr' => array(
                        'class' => 'tickets_date_dropdowns',
                        'onchange' => 'loadServiceResponse(\'' . $this->reloadUrl . '\', this.value, \'ticketTypes\', \'' . 'evp_bundle_ticketbundle_order_ticketTypes_ticketType' . '\')'
                    ),
                )
            )
            ->add(
                'ticketTypes',
                $this->ticketTypeCountForm,
                array(
                    'attr' => array(
                        'class' => 'multiSelect'
                    )
                )
            )
            ->add(
                'user',
                'email',
                array(
                    'label' => $this->translator->trans(OrderDetails::LABEL_USER_EMAIL, array(), 'columns')
                )
            )
            ->add(
                'save',
                'submit',
                array(
                    'attr' => array(
                        'class' => 'tickets_btn_submit',
                    ),
                    'label' => $this->translator->trans('admin.button.save', array(), 'columns'),
                    'disabled' => $this->disableSubmit,
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
        return 'evp_bundle_ticketbundle_order';
    }

    /**
     * @param Translator $translator
     */
    public function setTranslator(Translator $translator) {
        $this->translator = $translator;
    }
}
