<?php
/**
 * MenuManagerCompilerPass for collecting all Menu services
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MenuManagerCompilerPass
 */
class MenuManagerCompilerPass implements CompilerPassInterface
{
    const MENU_MANAGER = 'evp.ticket_admin.service.menu_manager';
    const MENU_TAG = 'evp.ticket_admin.menu_item';
    const MENU_ALIAS = 'alias';

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::MENU_MANAGER)) {
            return;
        }
        $menuManager = $container->getDefinition(self::MENU_MANAGER);
        $taggedServices = $container->findTaggedServiceIds(self::MENU_TAG);
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $menuManager->addMethodCall(
                    'addMenuItem',
                    array(
                        new Reference($id),
                        $attributes[self::MENU_ALIAS],
                    )
                );
            }
        }
    }
} 
