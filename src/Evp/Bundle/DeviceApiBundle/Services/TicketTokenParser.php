<?php
namespace Evp\Bundle\DeviceApiBundle\Services;

/**
 * Parses the ticket token from various sources
 *
 * Class TicketTokenParser
 * @package Evp\Bundle\DeviceApiBundle\Services
 */
class TicketTokenParser
{
    /**
     * @var string
     */
    private $ticketCheckerUrlPattern;

    /**
     * @param $ticketCheckerUrlPattern
     */
    function __construct($ticketCheckerUrlPattern)
    {
        $this->ticketCheckerUrlPattern = $ticketCheckerUrlPattern;
    }

    /**
     * Parses the ticket token from a base64 encoded ticket checker url
     *
     * @param string $base64EncodedUrl
     * @return string
     * @throws \Exception
     */
    public function parseFromBase64Url($base64EncodedUrl)
    {
        $url = base64_decode($base64EncodedUrl);
        $matches = array();
        $this->throwOnInvalidBase64Url($base64EncodedUrl, $url);

        if (preg_match($this->ticketCheckerUrlPattern, $url, $matches)) {
            return $matches[1];
        }

        $this->throwOnInvalidApiStringFormat($url);
    }

    /**
     * @param $base64EncodedUrl
     * @param $url
     * @throws \Exception
     */
    private function throwOnInvalidBase64Url($base64EncodedUrl, $url)
    {
        if (!$url) {
            throw new \Exception(
                sprintf(
                    'Invalid base64-encoded url detected: %s',
                    $base64EncodedUrl
                )
            );
        }
    }

    /**
     * @param $url
     * @throws \Exception
     */
    private function throwOnInvalidApiStringFormat($url)
    {
        throw new \Exception(
            sprintf(
                'Invalid api string format detected: expected %s, got %s',
                $this->ticketCheckerUrlPattern,
                $url
            )
        );
    }
} 