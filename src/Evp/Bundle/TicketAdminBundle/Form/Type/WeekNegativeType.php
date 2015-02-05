<?php

namespace Evp\Bundle\TicketAdminBundle\Form\Type;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class WeekNegativeType
 */
class WeekNegativeType extends AbstractType
{
    const MAX_WEEKS = 15;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @param Translator $trans
     */
    public function __construct(Translator $trans)
    {
        $this->translator = $trans;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = array();
        $slug = 'week';
        for ($i = 1; $i <= self::MAX_WEEKS; $i++) {
            if ($i >= 10) {
                $slug = 'weeks';
            }

            $choices['-' .$i .' week'] = $i .' '. $this->translator->transChoice(
                    '{1} week|]2,9] weeks2|]10,Inf] weeks',
                    $i,
                    array(),
                    'settings'
                );
        }
        $resolver->setDefaults(array(
                'choices' => $choices,
            ));
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'week_negative';
    }
} 
