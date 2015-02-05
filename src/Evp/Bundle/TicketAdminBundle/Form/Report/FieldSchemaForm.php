<?php
/**
 * Provides Report form for FieldSchema report
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Form\Report;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Form\FieldSchema;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class FieldSchemaForm
 */
class FieldSchemaForm extends AbstractType
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Form\FieldSchema[]
     */
    private $schemas;

    /**
     * @var string
     */
    private $reloadUrl;

    /**
     * @var array
     */
    private $parent;

    /**
     * Sets the parameters for Form
     *
     * @param array $params
     */
    public function setParameters($params) {
        $this->translator = $params['translator'];
        $this->schemas = $params['schemas'];
        $this->reloadUrl = $params['reloadUrl'];
        $this->parent = $params['parent'];
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return FormBuilderInterface
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $parent = $this->parent;
        $builder->add(
                'isRequiredForAll',
                'choice',
                array(
                    'required' => true,
                    'label' => $this->translator->trans(FieldSchema::LABEL_SCHEMA_TYPE, array(), 'columns'),
                    'choices' => array(
                        0 => $this->translator->trans(FieldSchema::LABEL_SCHEMA_GENERAL, array(), 'columns'),
                        1 => $this->translator->trans(FieldSchema::LABEL_SCHEMA_COMMON, array(), 'columns')
                    ),
                    'attr' => array(
                        'onchange' => 'loadServiceResponse(\'' . $this->reloadUrl . '\', this.value, \'schema_type_fields\', \'' . $this->getName() . '_fieldSchemas' . '\')',
                    )
                )
            );
        $builder->add(
            'fieldSchemas',
            'choice',
            array(
                'expanded' => false,
                'multiple' => true,
                'label' => $this->translator->trans(FieldSchema::LABEL_NAME, array(), 'columns'),
                'choices' => $this->schemas,
            )
        );
        $builder->add(
            'ticketType',
            'entity',
            array(
                'class' => 'EvpTicketBundle:TicketType',
                'property' => 'name',
                'label' => $this->translator->trans(TicketType::LABEL_NAME, array(), 'columns'),
                'translation_domain' => 'columns',
                'query_builder' => function(EntityRepository $er) use ($parent) {
                        return $er->createQueryBuilder('tt')
                            ->where('tt.status = 1')
                            ->andWhere('tt.event = :ev')
                            ->setParameters(
                                array(
                                    'ev' => $parent['id'],
                                )
                            );
                    },
                'mapped' => false,
                'attr' => array(
                    'name' => 'type[]',
                )
            )
        );
        $builder->add(
            'filter',
            'submit',
            array(
                'label' => $this->translator->trans('admin.actions.filter_results', array(), 'columns'),
            )
        );
        return $builder;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'admin_report_field_schema';
    }
}
