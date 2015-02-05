<?php
/**
 * Bearer Security factory
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\DeviceApiBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class BearerFactory
 * @package Evp\Bundle\DeviceApiBundle\DependencyInjection\Security\Factory
 */
class BearerFactory implements SecurityFactoryInterface {

    /**
     * {@inheritdoc}
     *
     * @param ContainerBuilder $container
     * @param $id
     * @param $config
     * @param $userProvider
     * @param $defaultEntryPoint
     */
    public function create(
        ContainerBuilder $container,
        $id,
        $config,
        $userProvider,
        $defaultEntryPoint
    ) {
        $providerId = 'security.authentication.provider.bearer.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('evp_device_api.authentication.bearer_provider'))
            ->replaceArgument(0, new Reference($userProvider));

        $listenerId = 'security.authentication.listener.bearer.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('evp_device_api.authentication.bearer_listener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    /**
     * [@inheritdoc}
     *
     * @return string
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getKey()
    {
        return 'bearer';
    }

    /**
     * {@inheritdoc}
     *
     * @param NodeDefinition $node
     */
    public function addConfiguration(NodeDefinition $node)
    {
    }
} 