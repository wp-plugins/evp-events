<?php

namespace Evp\Bundle\PaymentBundle;

use Evp\Bundle\PaymentBundle\CompilerPass\PaymentHandlerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EvpPaymentBundle extends Bundle
{
    public function build(ContainerBuilder $container) {
        parent::build($container);

        $container->addCompilerPass(new PaymentHandlerCompilerPass());
    }
}
