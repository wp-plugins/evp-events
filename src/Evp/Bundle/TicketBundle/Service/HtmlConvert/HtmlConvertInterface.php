<?php
/**
 * Interface for HTML to PDF conversion
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\HtmlConvert;

/**
 * Interface HtmlToPdfInterface
 */
interface HtmlConvertInterface
{
    const OUTPUT_PDF = 'pdf';
    const OUTPUT_HTML = 'html';

    /**
     * Updates the current Email body according to Converter
     *
     * @param string $body
     *
     * @return string
     */
    function updateBody($body);

    /**
     * Adds the attachment from given URL
     *
     * @param string $url
     * @param string $title
     *
     * @return \Swift_Mime_MimeEntity
     */
    function addAttachment($url, $title);

    /**
     * Gets the supported output format for current Converter
     *
     * @return string
     */
    function getOutputFormat();

    /**
     * Renders given URL
     *
     * @param string $url
     *
     * @return string
     */
    function renderUrl($url);
}
