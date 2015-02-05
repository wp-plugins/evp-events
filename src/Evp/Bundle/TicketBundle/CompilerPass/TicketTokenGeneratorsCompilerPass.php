<?php
/**
 * Adds available Ticket token generators
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TicketTokenGeneratorsCompilerPass
 */
class TicketTokenGeneratorsCompilerPass implements CompilerPassInterface
{
    const TOKEN_MANAGER = 'evp.service.ticket_token_manager';
    const TAG_NAME = 'ticket_token_provider';
    const PROP_NAME = 'type';
    const PRIORITY_NAME = 'priority';

    /**
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::TOKEN_MANAGER)) {
            return;
        }

        $service = $container->getDefinition(self::TOKEN_MANAGER);
        $taggedServices = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (empty($attributes[self::PROP_NAME]) || empty($attributes[self::PRIORITY_NAME])) {
                    throw new \Exception('Service must have its Type and Priority named ' .$id);
                }
                $generatorDef = $container->getDefinition($id);
                $generatorDef->addMethodCall('setPriority', array($attributes[self::PRIORITY_NAME]));

                $service->addMethodCall(
                    'addGenerator',
                    array(new Reference($id), $attributes[self::PROP_NAME], $attributes[self::PRIORITY_NAME])
                );
            }
        }
        $service->addMethodCall('sortGenerators');
    }
} 
