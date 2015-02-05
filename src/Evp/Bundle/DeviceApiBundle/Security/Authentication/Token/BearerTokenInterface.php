<?php
/**
 * Bearer token recognition interface
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\DeviceApiBundle\Security\Authentication\Token;

/**
 * Interface BearerTokenInterface
 * @package Evp\Bundle\DeviceApiBundle\Security\Authentication\Token
 */
interface BearerTokenInterface {

    /**
     * Gets the Token to validate
     *
     * @return string
     */
    function getToken();
} 