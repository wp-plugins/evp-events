<?php
/**
 * Bearer authentication provider
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\DeviceApiBundle\Security\Authentication\Provider;

use Evp\Bundle\DeviceApiBundle\Entity\User\BearerUserInterface;
use Evp\Bundle\DeviceApiBundle\Security\Authentication\Token\BearerTokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class BearerProvider
 * @package Evp\Bundle\DeviceApiBundle\Security\Authentication\Provider
 */
class BearerProvider implements AuthenticationProviderInterface {

    /**
     * @var \Evp\Bundle\DeviceApiBundle\Security\User\BearerUserProvider
     */
    private $userProvider;

    /**
     * @param UserProviderInterface $provider
     */
    public function __construct(UserProviderInterface $provider) {
        $this->userProvider = $provider;
    }

    /**
     * {@inheritdoc}
     *
     * @param TokenInterface $token
     * @throws AuthenticationException
     * @return TokenInterface
     */
    public function authenticate(TokenInterface $token) {
        $user = $this->userProvider->loadUserByUsername($token->getToken());

        if ($user instanceof BearerUserInterface) {
                $authenticatedToken = clone $token;
                $authenticatedToken->setUser($user);
                $authenticatedToken->setAuthenticated(true);

                return $authenticatedToken;
            }
        throw new AuthenticationException('Bearer authentication failed for token ' .$token->getToken());
    }

    /**
     * {@inheritdoc}
     *
     * @param TokenInterface $token
     * @return bool
     */
    public function supports(TokenInterface $token) {
        return $token instanceof BearerTokenInterface;
    }
} 