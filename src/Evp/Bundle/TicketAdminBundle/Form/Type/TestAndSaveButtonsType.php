<?php

namespace Evp\Bundle\TicketAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TestAndSaveButtonsType
 */
class TestAndSaveButtonsType extends AbstractType
{
    /**
     * @var string
     */
    private $testingUrl;

    /**
     * @param $testingUrl
     */
    public function __construct($testingUrl)
    {
        $this->testingUrl = $testingUrl;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return FormBuilderInterface
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('testConnection', 'button', array(
                'label' => 'admin.parameters.test_connection',
                'attr' => array(
                    'test-url' => $this->testingUrl,
                ),
            ));
        $builder->add('save', 'submit', array(
                'label' => 'admin.parameters.save'
            ));

        return $builder;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_parameters_test_and_save_type';
    }
} 
