<?php

namespace Evp\Bundle\TicketAdminBundle\Form\Type;

use Evp\Bundle\TicketAdminBundle\Form\Transformer\MailerEncryptionTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class MailerEncryptionType
 */
class MailerEncryptionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new MailerEncryptionTransformer());
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
        return 'mailer_encryption';
    }
}
