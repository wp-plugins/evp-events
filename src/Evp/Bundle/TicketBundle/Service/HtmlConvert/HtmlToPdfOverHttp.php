<?php
/**
 * Prints HTML contents to PDF using Remote site
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service\HtmlConvert;

use Symfony\Bridge\Monolog\Logger;

/**
 * Class HtmlToPdfOverHttp
 */
class HtmlToPdfOverHttp implements HtmlConvertInterface
{
    const POST_PARAM_NAME = 'content';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $remoteUri;

    /**
     * @var string
     */
    private $authHeader;

    /**
     * @param Logger $log
     * @param string $remoteUri
     * @param string $authHeader
     */
    public function __construct(
        Logger $log,
        $remoteUri,
        $authHeader
    ) {
        $this->logger = $log;
        $this->remoteUri = $remoteUri;
        $this->authHeader = $authHeader;
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
        return $body;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $url
     *
     * @return \Swift_Mime_MimeEntity
     */
    public function addAttachment($url, $title)
    {
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, $url);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($handler);
        return \Swift_Attachment::newInstance($result)->setFilename($title);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getOutputFormat()
    {
        return HtmlConvertInterface::OUTPUT_PDF;
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
        $this->logger->debug(__CLASS__ .' - Trying to print Ticket PDF for URL', array($url));
        $contentHandler = curl_init();
        curl_setopt($contentHandler, CURLOPT_URL, $url);
        curl_setopt($contentHandler, CURLOPT_RETURNTRANSFER, true);
        $htmlContents = curl_exec($contentHandler);
        $contents = urlencode($htmlContents);

        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, $this->remoteUri);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_POST, true);
        curl_setopt($handler, CURLOPT_HTTPHEADER, array($this->authHeader));
        curl_setopt($handler, CURLOPT_POSTFIELDS, self::POST_PARAM_NAME .'=' .$contents);
        $result = curl_exec($handler);

        $this->logger->debug('Got URL contents with length ' .strlen($result));

        return $result;
    }
}
