<?php
namespace Evp\Bundle\PaymentBundle\Service;
use Doctrine\Common\Collections\ArrayCollection;
use Evp\Bundle\PaymentBundle\Entity\PaymentPreferences;
use Evp\Bundle\PaymentBundle\Entity\PaymentType;
use Evp\Bundle\PaymentBundle\PaymentHandler\HandlerAbstract;
use Evp\Bundle\PaymentBundle\PaymentHandler\HandlerInterface;
use Evp\Bundle\TicketBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PaymentHandlerProvider
 * @package Evp\Bundle\PaymentBundle\Service
 */
class PaymentHandlerProvider {
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var ArrayCollection
     */
    private $taggedServices;
    /**
     * @param ContainerInterface $container
     */
    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->taggedServices = new ArrayCollection();
    }
    /**
     * @return array
     */
    public function getAllTaggedServices() {
        return $this->taggedServices;
    }

    /**
     * @param $service
     */
    public function addTaggedService($service) {
        $this->taggedServices->add($service);
    }

    /**
     * Find a handler by name
     *
     * @param $handlerName
     * @return HandlerInterface
     */
    public function getHandlerFromName($handlerName) {
        /** @var HandlerInterface $paymentHandler */
        foreach ($this->getAllTaggedServices() as $paymentHandler) {
            if ($paymentHandler->getName() === $handlerName) {
                return $paymentHandler;
            }
        }
    }

    /**
     * @param PaymentType $requestedPaymentType
     * @param User $user
     * @return HandlerInterface|null
     */
    public function getHandlerForPaymentTypeAndUser(PaymentType $requestedPaymentType, User $user) {
        /** @var HandlerInterface $paymentHandler */
        foreach ($this->getAllTaggedServices() as $paymentHandler) {
            $paymentTypes = $paymentHandler->getPaymentTypesForUser($user);

            foreach ($paymentTypes as $paymentType) {
                if ($requestedPaymentType->getName() == $paymentType->getName()) {
                    return $paymentHandler;
                }
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function sortServicesByRank() {
        $iterator = $this->taggedServices->getIterator();

        $iterator->uasort(function (HandlerAbstract $a, HandlerAbstract $b) {
                return $a->getRank() - $b->getRank();
            }
        );

        $this->taggedServices = new ArrayCollection(iterator_to_array($iterator));
    }
} 