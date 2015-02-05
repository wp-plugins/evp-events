<?php
/**
 * Adds available HTML converters
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class HtmlConverterCompilerPass
 */
class HtmlConverterCompilerPass implements CompilerPassInterface
{
    const HTML_CONVERT_MANAGER = 'evp.service.html_convert';
    const CONVERTER_TAG_NAME = 'evp.html_converter';
    const CONVERTER_NAME = 'converter';

    /**
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::HTML_CONVERT_MANAGER)) {
            return;
        }

        $service = $container->getDefinition(self::HTML_CONVERT_MANAGER);
        $taggedServices = $container->findTaggedServiceIds(self::CONVERTER_TAG_NAME);

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (empty($attributes[self::CONVERTER_NAME])) {
                    throw new \Exception('Service must have its converter type named ' .$id);
                }
                $service->addMethodCall('addHtmlConverter', array(new Reference($id), $attributes[self::CONVERTER_NAME]));
            }
        }
    }
}
