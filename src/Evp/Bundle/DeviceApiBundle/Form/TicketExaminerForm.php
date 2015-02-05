<?php
/**
 * Provides  TicketExaminerForm for device pairing
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\DeviceApiBundle\Form;

use Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TicketExaminerForm
 */
class TicketExaminerForm extends AbstractType
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
                'name',
                'text',
                array(
                    'label' => $this->translator->trans(TicketExaminer::LABEL_NAME, array(), 'columns'),
                )
            )
            ->add(
                'textUnused',
                'text',
                array(
                    'label' => $this->translator->trans(TicketExaminer::LABEL_TEXT_UNUSED, array(), 'columns'),
                )
            )
            ->add(
                'textUsed',
                'text',
                array(
                    'label' => $this->translator->trans(TicketExaminer::LABEL_TEXT_USED, array(), 'columns'),
                )
            )
            ->add(
                'save',
                'submit',
                array(
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
                'data_class' => 'Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer'
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_event_device_ticket_examiner';
    }
}
