<?php

namespace Evp\Bundle\PaymentBundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PaymentHandlerCompilerPass implements CompilerPassInterface
{
    const PAYMENT_HANDLER_PROVIDER_ID = 'evp_payment.service.payment_handler_provider';
    const PAYMENT_HANDLER_TAG = 'evp.payment_handler';
    const PAYMENT_IMPORTANCE_ATTRIBUTE = 'rank';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::PAYMENT_HANDLER_PROVIDER_ID)) {
            return;
        }

        $providerDefinition = $container->getDefinition(self::PAYMENT_HANDLER_PROVIDER_ID);
        $taggedServices = $container->findTaggedServiceIds(self::PAYMENT_HANDLER_TAG);

        foreach ($taggedServices as $id => $tagAttributes) {
            $serviceDefinition = $container->getDefinition($id);

            foreach ($tagAttributes as $attributes) {
                if (empty($attributes[self::PAYMENT_IMPORTANCE_ATTRIBUTE])) {
                    throw new \Exception('No importance attribute defined for payment_handler_provider');
                }

                $serviceDefinition->addMethodCall('setRank', array($attributes[self::PAYMENT_IMPORTANCE_ATTRIBUTE]));
                $serviceDefinition->addMethodCall('setName', array($id));
                $providerDefinition->addMethodCall('addTaggedService', array(new Reference($id)));
            }

            $providerDefinition->addMethodCall('sortServicesByRank');
        }
    }
} 