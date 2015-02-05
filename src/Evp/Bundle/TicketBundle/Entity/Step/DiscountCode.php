<?php

namespace Evp\Bundle\TicketBundle\Entity\Step;

/**
 * Class DiscountCode
 * @package Evp\Bundle\TicketBundle\Entity\Step
 */
class DiscountCode {
    /**
     * @var string
     */
    private $token;

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = strtoupper($token);
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return strtoupper($this->token);
    }

    public static function create()
    {
        return new self();
    }
}
