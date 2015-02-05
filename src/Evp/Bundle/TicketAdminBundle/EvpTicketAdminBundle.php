<?php

namespace Evp\Bundle\TicketAdminBundle;

use Evp\Bundle\TicketAdminBundle\CompilerPass\MenuManagerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EvpTicketAdminBundle extends Bundle
{
    /**
     * Builds CompilerPass for MenuManager
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new MenuManagerCompilerPass);
    }
}
