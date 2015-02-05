<?php
/**
 * Gives correct HTML conversion service in system
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Evp\Bundle\TicketBundle\Service\HtmlConvert\HtmlConvertInterface;

/**
 * Class HtmlConvert
 */
class HtmlConvert
{
    /**
     * @var HtmlConvertInterface
     */
    private $htmlConverters;

    /**
     * @param HtmlConvertInterface $converter
     * @param                      $name
     */
    public function addHtmlConverter(HtmlConvertInterface $converter, $name)
    {
        $this->htmlConverters[$name] = $converter;
    }

    /**
     * Gets the current HTML converter
     *
     * @param string $name
     *
     * @return HtmlConvertInterface
     */
    public function getCurrentConverter($name)
    {
        return $this->htmlConverters[$name];
    }
} 
