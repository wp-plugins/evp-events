<?php
/**
 * Prints HTML contents back to parent HTML
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\HtmlConvert;

use Symfony\Bridge\Monolog\Logger;

/**
 * Class HtmlToHtml
 */
class HtmlToHtml implements HtmlConvertInterface
{
    const BODY_SPACE = "\n\n\n</br></br></br>";

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $log
     */
    public function __construct(
        Logger $log
    ) {
        $this->logger = $log;
    }

    /**
     * @var string
     */
    private $ticketHtml;

    /**
     * {@inheritdoc}
     *
     * @param string $url
     *
     * @return \Swift_Mime_MimeEntity
     */
    public function addAttachment($url, $title)
    {
        $this->ticketHtml = file_get_contents($url);
        return \Swift_Attachment::newInstance();
    }

    /**
     * {@inheritdoc}
     *
     * @param $body
     *
     * @return string
     */
    public function updateBody($body)
    {
        return $body .self::BODY_SPACE .$this->ticketHtml;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getOutputFormat()
    {
        return HtmlConvertInterface::OUTPUT_HTML;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $url
     *
     * @return string
     */
    public function renderUrl($url)
    {
        $this->logger->debug(__CLASS__ .' - Trying to print Ticket HTML for URL', array($url));
        $result = file_get_contents($url);
        $this->logger->debug('Got URL contents with length ' .strlen($result));

        return $result;
    }
}
