<?php
/**
 * Adds available Steps
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class StepServicesCompilerPass
 */
class StepServicesCompilerPass implements CompilerPassInterface
{
    const STEP_MANAGER = 'evp.service.step_manager';
    const STEP_TAG_NAME = 'step_service';
    const NAME = 'step';

    /**
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::STEP_MANAGER)) {
            return;
        }

        $service = $container->getDefinition(self::STEP_MANAGER);
        $taggedServices = $container->findTaggedServiceIds(self::STEP_TAG_NAME);

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (empty($attributes[self::NAME])) {
                    throw new \Exception('Service must have its Step named ' .$id);
                }
                $service->addMethodCall('addStepService', array(new Reference($id), $attributes[self::NAME]));
            }
        }
    }
}
