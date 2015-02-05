<?php
/**
 * Adds available Ticket code generator strategies
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TicketCodeStrategiesCompilerPass
 */
class TicketCodeStrategiesCompilerPass implements CompilerPassInterface
{
    const CODE_GENERATOR = 'evp.service.ticket_code_generator';
    const STRATEGY_TAG_NAME = 'ticket_code_strategy';
    const NAME = 'strategy';

    /**
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::CODE_GENERATOR)) {
            return;
        }

        $service = $container->getDefinition(self::CODE_GENERATOR);
        $taggedServices = $container->findTaggedServiceIds(self::STRATEGY_TAG_NAME);

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (empty($attributes[self::NAME])) {
                    throw new \Exception('Service must have its Step named ' .$id);
                }
                $service->addMethodCall('addCodeStrategy', array(new Reference($id), $attributes[self::NAME]));
            }
        }
    }
} 
