<?php
/**
 * Adds available Reports
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\ReportingBundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ReportsCompilerPass
 */
class ReportsCompilerPass implements CompilerPassInterface
{
    const MANAGER = 'evp_reporting.report_manager';
    const TAG_NAME = 'evp.report';

    /**
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::MANAGER)) {
            return;
        }

        $service = $container->getDefinition(self::MANAGER);
        $taggedServices = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($taggedServices as $id => $tagAttributes) {
                $service->addMethodCall('addReport', array(new Reference($id)));
        }
    }
}
