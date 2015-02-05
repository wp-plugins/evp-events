<?php
/**
 * Provides filter Form for filtering Entities in Admin
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class GenericFilterForm
 */
class GenericFilterForm extends AbstractType
{
    /**
     * @var array
     */
    private $columns;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * @param array $columns
     */
    function __construct(array $columns = null) {
        $this->columns = $columns;
    }

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
    public function buildForm(FormBuilderInterface $builder, array $options) {

        foreach ($this->columns as $name) {
            $builder->add(
                $name,
                'text',
                array(
                    'required' => false,
                    'label' => false,
                )
            );
        }
        return $builder->add(
            'filter',
            'submit',
            array(
                'label' => $this->translator->trans('admin.actions.filter_results', array(), 'columns'),
            )
        );
    }

    /**
     * @return string
     */
    public function getName() {
        return 'admin_filter_entity';
    }
}
