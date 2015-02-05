<?php

namespace Evp\Bundle\TicketAdminBundle\Form\Type;

use Evp\Bundle\TicketAdminBundle\Form\Transformer\BearerTokenTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class BearerTokenType
 */
class BearerTokenType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new BearerTokenTransformer());
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bearer_token';
    }
}
