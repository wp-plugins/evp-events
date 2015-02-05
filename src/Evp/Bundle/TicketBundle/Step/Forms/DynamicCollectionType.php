<?php

namespace Evp\Bundle\TicketBundle\Step\Forms;

use Evp\Bundle\TicketBundle\Entity\User;
use Evp\Bundle\TicketBundle\Step\UserDetailsFill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DynamicCollectionType
 * @package Evp\Bundle\TicketBundle\Step\Forms
 * @author d.glezeris
 */
class DynamicCollectionType  extends AbstractType {

    private $fieldSchemasCommon;
    private $fieldSchemasGlobal;

    function __construct($fieldSchemasGlobal, $fieldSchemasCommon)
    {
        $this->fieldSchemasGlobal = $fieldSchemasGlobal;
        $this->fieldSchemasCommon = $fieldSchemasCommon;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $this->buildGlobalFields($builder);
        $this->buildCommonFields($builder);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
                'data_class' => 'Evp\Bundle\TicketBundle\Entity\Dynamic\Collection',
                'translation_domain' => 'messages',
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'evp_bundle_ticketbundle_dynamic_collection_type';
    }

    /**
     * @param FormBuilderInterface $builder
     */
    private function buildGlobalFields(FormBuilderInterface $builder)
    {
        if (count($this->fieldSchemasGlobal) !== 0) {
            $globalDetailsFormType = new GlobalUserDetails();
            $globalDetailsFormType->setFieldSchemasGlobal($this->fieldSchemasGlobal);

            $builder->add(
                'globalDetails',
                'collection',
                array(
                    'type' => $globalDetailsFormType,
                    'label' => 'label.user_details.global_details'
                )
            );
        }
    }

    /**
     * @param FormBuilderInterface $builder
     */
    private function buildCommonFields(FormBuilderInterface $builder)
    {
        $fieldSchemasWithoutHiddenFields = $this->filterHiddenFields();

        if (count($fieldSchemasWithoutHiddenFields)  !== 0) {
            $commonDetailsFormType = new CommonUserDetails();
            $commonDetailsFormType->setFieldSchemasCommon($this->fieldSchemasCommon);

            $builder->add(
                'commonDetails',
                'collection',
                array(
                    'type' => $commonDetailsFormType,
                    'label' => 'label.user_details.common_details'
                )
            );
        }
    }

    /**
     * @return array
     */
    private function filterHiddenFields()
    {
        $fieldSchemasWithoutHiddenFields = array_filter(
            $this->fieldSchemasCommon,
            function ($element) {
                return is_object($element);
            }
        );
        return $fieldSchemasWithoutHiddenFields;
    }

}
