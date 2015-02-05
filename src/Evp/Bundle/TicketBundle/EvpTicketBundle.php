<?php

namespace Evp\Bundle\TicketBundle;

use Evp\Bundle\TicketBundle\CompilerPass\HtmlConverterCompilerPass;
use Evp\Bundle\TicketBundle\CompilerPass\StepServicesCompilerPass;
use Evp\Bundle\TicketBundle\CompilerPass\TicketCodeStrategiesCompilerPass;
use Evp\Bundle\TicketBundle\CompilerPass\TicketTokenGeneratorsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EvpTicketBundle extends Bundle
{
    public function build(ContainerBuilder $container) {
        parent::build($container);

        $container->addCompilerPass(new HtmlConverterCompilerPass());
        $container->addCompilerPass(new StepServicesCompilerPass());
        $container->addCompilerPass(new TicketCodeStrategiesCompilerPass());
        $container->addCompilerPass(new TicketTokenGeneratorsCompilerPass());
    }
}
