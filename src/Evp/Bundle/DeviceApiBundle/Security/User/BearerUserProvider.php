<?php
/**
 * User provider for Bearer authentication
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\DeviceApiBundle\Security\User;

use Evp\Bundle\DeviceApiBundle\Entity\User\BearerUserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class BearerUserProvider
 * @package Evp\Bundle\DeviceApiBundle\Security\User
 */
class BearerUserProvider implements UserProviderInterface {

    /**
     * @var \Evp\Bundle\DeviceApiBundle\Security\User\HandlerInterface[]
     */
    private $handlers;

    /**
     * @param \Evp\Bundle\DeviceApiBundle\Security\User\HandlerInterface[] $handlers
     */
    public function setHandlers($handlers) {
        $this->handlers = $handlers;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $token
     * @return string|\Symfony\Component\Security\Core\User\UserInterface
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($token) {
        foreach ($this->handlers as $handler) {
            if ($handler->validate($token)) {

                return $handler->getEntity();
            }
        }
        throw new UsernameNotFoundException(sprintf('User with token "%s" does not exist.', $token));
    }

    /**
     * {@inheritdoc}
     *
     * @param UserInterface $user
     * @return string|UserInterface
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function refreshUser(UserInterface $user) {
        if (!$user instanceof BearerUserInterface) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getToken());
    }

    /**
     * {@inheritdoc}
     *
     * @param string $class
     * @return bool
     */
    public function supportsClass($class) {
        $temp = new $class;
        return $temp instanceof BearerUserInterface;
    }
}
