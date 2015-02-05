<?php

namespace Evp\Bundle\TicketAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OptionalType
 */
class OptionalType extends AbstractType
{
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
        return 'optional';
    }
}
