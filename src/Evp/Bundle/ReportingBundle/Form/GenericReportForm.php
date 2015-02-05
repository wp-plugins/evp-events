<?php

namespace Evp\Bundle\ReportingBundle\Form;

use Evp\Bundle\ReportingBundle\Service\Report\ReportInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class GenericReportForm
 */
class GenericReportForm extends AbstractType
{
    const FORM_NAME = 'report_generic_form';
    const REPORT_NAME = 'report';

    /**
     * @var array
     */
    private $reportChoices;

    /**
     * @var ReportInterface
     */
    private $report;

    /**
     * @var string
     */
    private $fieldsUrl;

    /**
     * @param array $reportChoices
     * @param string $fieldsUrl
     */
    public function __construct($reportChoices, $fieldsUrl)
    {
        $this->reportChoices = $reportChoices;
        $this->fieldsUrl = $fieldsUrl;
    }

    /**
     * @param ReportInterface $report
     */
    public function setReport(ReportInterface $report)
    {
        $this->report = $report;
    }

    /**
     * {@inheritdoc}
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('event', 'entity', array(
                    'class' => 'EvpTicketBundle:Event',
                    'property' => 'name',
                    'label' => 'report.event_label',
                ))
            ->add('dateFrom', 'date', array(
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'label' => 'report.date_from_label',
                ))
            ->add('dateTo', 'date', array(
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'label' => 'report.date_to_label',
                ))
            ->add(self::REPORT_NAME, 'choice', array(
                    'choices' => $this->reportChoices,
                    'label' => 'report.' .self::REPORT_NAME .'_label',
                    'attr' => array(
                        'fields-url' => $this->fieldsUrl,
                    ),
                ))
            ->add('includeTests', 'choice', array(
                    'choices' => array('true' => 'report.include_test_orders'),
                    'required' => false,
                    'expanded' => true,
                    'multiple' => true,
                    'label' => false,
                ));
        if ($this->report !== null) {
            $this->report->injectFormElements($builder);
        }
        $builder->add('submit', 'submit', array('label' => 'report.submit_form'));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'translation_domain' => 'forms',
            ));
    }

    /**
     * {@inheritdoc}
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::FORM_NAME;
    }
}
