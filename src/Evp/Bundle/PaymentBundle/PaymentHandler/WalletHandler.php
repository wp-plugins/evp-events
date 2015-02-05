<?php

namespace Evp\Bundle\PaymentBundle\PaymentHandler;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\PaymentBundle\Entity as Entities;
use Evp\Bundle\TIcketBundle\Entity as TicketEntities;
use Evp\Bundle\TicketBundle\Service\UserSession;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class WalletHandler extends HandlerAbstract
{
    const PAYMENT_NAME = 'wallet';
    const PAYMENT_TITLE = 'payment.title.wallet';


    /**
     * @param Router $router
     * @param UserSession $userSession
     * @param Logger $logger
     * @param EntityManager $entityManager
     */
    function __construct(
        Router $router,
        UserSession $userSession,
        Logger $logger,
        EntityManager $entityManager
    ) {
        parent::__construct(
            $router,
            $userSession,
            $logger,
            $entityManager
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentTypesForUser($user)
    {
        $paymentTypes = array();

        $paymentTypes[self::PAYMENT_NAME] = Entities\PaymentType::create()
            ->setName(self::PAYMENT_NAME)
            ->setTitle(self::PAYMENT_TITLE)
            ->setLogoUrl('https://www.mokejimai.lt/new/upload/plan_payment_types/rf50add916c396f/mokejimai.png')
            ->setHandlerClass($this->getName());

        return $paymentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentResponseForUser($user, $paymentType)
    {
        $url = '';
        $status = 302;

        return new RedirectResponse($url, $status);
    }
} 
