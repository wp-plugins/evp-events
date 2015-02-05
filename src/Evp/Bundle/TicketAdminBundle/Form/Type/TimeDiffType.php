<?php

namespace Evp\Bundle\TicketAdminBundle\Form\Type;

use Evp\Bundle\TicketAdminBundle\Form\Transformer\RelativeTimeToTimeStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TimeDiffType
 */
class TimeDiffType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new RelativeTimeToTimeStringTransformer);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'time';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'time_diff';
    }
}
