<?php

namespace Evp\Bundle\TicketBundle\Form;

use Evp\Bundle\TicketBundle\Entity\Template;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TemplateForm
 * @package Evp\Bundle\TicketBundle\Form
 */
class TemplateForm extends AbstractType
{
    private $locales;
    private $reloadUrl;
    private $types;
    private $currentLocale;

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
        $this->locales = $params['locales'];
        $this->translator = $params['translator'];
        $this->reloadUrl = $params['reloadUrl'];
        $this->types = $params['types'];
        $this->currentLocale = $params['currentLocale'];
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'locale',
                'choice',
                array(
                    'choices' => $this->locales,
                    'expanded' => false,
                    'data' => $this->currentLocale,
                    'attr' => array(
                        'class' => 'tickets_date_dropdowns',
                        'onchange' => 'refreshEntityBasedOnTargetLocale(\'' . $this->reloadUrl . '\', this.value, \'' . $this->getName() . '\')',
                        'label' => $this->translator->trans(Template::LABEL_LOCALE, array(), 'columns')
                    )
                )
            )
            ->add(
                'type',
                'choice',
                array(
                    'label' => $this->translator->trans(Template::LABEL_TYPE, array(), 'columns'),
                    'choices' => $this->types,
                    'translation_domain' => 'columns',
                )
            )
            ->add(
                'name',
                'text',
                array(
                    'label' => $this->translator->trans(Template::LABEL_NAME, array(), 'columns'),
                )
            )
            ->add(
                'source',
                'ckeditor',
                array(
                    'label' => $this->translator->trans(Template::LABEL_SOURCE, array(), 'columns'),
                    'attr' => array(
                        'class' => 'ckeditor',
                    ),
                )
            )
            ->add(
                'fromEmail',
                'text',
                array(
                    'required' => false,
                    'label' => $this->translator->trans(Template::LABEL_FROM_EMAIL, array(), 'columns'),
                    'error_bubbling' => true,
                    'constraints' => array(
                        new Assert\Regex(array(
                            'pattern' => '/<?[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}>?$/',
                            'message' => 'message.error.template.email_not_valid',
                        ))
                    )
                )
            )
            ->add(
                'subject',
                'text',
                array(
                    'required' => false,
                    'label' => $this->translator->trans(Template::LABEL_SUBJECT, array(), 'columns'),
                )
            )
            ->add(
                'attachmentName',
                'text',
                array(
                    'required' => false,
                    'label' => $this->translator->trans(Template::LABEL_ATTACHMENT_NAME, array(), 'columns'),
                )
            )
            ->add(
                'save',
                'submit',
                array(
                    'attr' => array(
                        'class' => 'tickets_btn_submit'
                    ),
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
            'data_class' => 'Evp\Bundle\TicketBundle\Entity\Template'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_template';
    }
}
