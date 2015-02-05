<?php

namespace Evp\Bundle\DeviceApiBundle;

use Evp\Bundle\DeviceApiBundle\DependencyInjection\Security\Factory\BearerFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EvpDeviceApiBundle
 * @package Evp\Bundle\DeviceApiBundle
 */
class EvpDeviceApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new BearerFactory());
    }
}
