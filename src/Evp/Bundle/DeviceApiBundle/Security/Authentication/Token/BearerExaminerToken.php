<?php
/**
 * Ticket Examiner Bearer token
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\DeviceApiBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Class BearerExaminerToken
 * @package Evp\Bundle\DeviceApiBundle\Security\Authentication\Token
 */
class BearerExaminerToken extends AbstractToken implements BearerTokenInterface {

    /**
     * @var string
     */
    protected $token;

    /**
     * Sets the token
     * @param $tk
     */
    public function setToken($tk) {
        $this->token = $tk;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getCredentials() {
        return '';
    }
}
