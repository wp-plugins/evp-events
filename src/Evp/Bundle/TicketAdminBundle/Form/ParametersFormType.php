<?php

namespace Evp\Bundle\TicketAdminBundle\Form;

use Evp\Bundle\TicketAdminBundle\Form\Type\TestAndSaveButtonsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ParametersFormType
 * @package Evp\Bundle\TicketAdminBundle\Form
 *
 * Creates a form that is used to edit parameters.yml
 */
class ParametersFormType extends AbstractType
{
    const HIDDEN_FIELD = 'hidden';

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $customTypes = array(
        'mailer_encryption',
    );
    /**
     * @var array
     */
    private $viewConfig;

    /**
     * @var string
     */
    private $testingUrl;

    /**
     * @param array  $parameters
     * @param array  $viewConfig
     * @param string $mailTestUrl
     */
    function __construct(array $parameters, array $viewConfig, $mailTestUrl)
    {
        $this->parameters = $parameters;
        $this->viewConfig = $viewConfig;
        $this->testingUrl = $mailTestUrl;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'translation_domain' => 'settings'
            ));

    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return FormBuilderInterface
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->parameters as $name => $value) {
            if (array_key_exists($name, $this->viewConfig)) {
                $fieldType = $this->viewConfig[$name];
                if ($fieldType === self::HIDDEN_FIELD) {
                    continue;
                } elseif (!is_array($fieldType)) {
                    $builder->add($name, $fieldType, array(
                            'required' => false,
                            'label' => $name,
                        ));
                } else {
                    $type = in_array($name, $this->customTypes) ? $name : 'choice';
                    $builder->add($name, $type, array(
                            'choices' => $fieldType,
                            'label' => $name,
                        ));
                }
            }
        }
        $builder->add('buttons', new TestAndSaveButtonsType($this->testingUrl), array(
                'label' => false,
                'mapped' => false,
            ));

        return $builder;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_parameters_form_type';
    }
}
