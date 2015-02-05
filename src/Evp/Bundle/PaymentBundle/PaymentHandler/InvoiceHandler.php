<?php

namespace Evp\Bundle\PaymentBundle\PaymentHandler;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\PaymentBundle\Entity as Entities;
use Evp\Bundle\TIcketBundle\Entity as TicketEntities;
use Evp\Bundle\TicketBundle\Service\UserSession;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;

class InvoiceHandler extends HandlerAbstract
{
    const PAYMENT_NAME = 'invoice';
    const PAYMENT_TITLE = 'payment.type.invoice';
    const PROFORMA_INVOICE = 'invoice_proforma';

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
        $isInvoiceEnabled = $user->getOrder()
            ->getEvent()
            ->getEventType()
            ->getPayByInvoice();

        $paymentTypes = array();

        if ($isInvoiceEnabled) {
            $paymentTypes[] = Entities\PaymentType::create()
                ->setName(self::PAYMENT_NAME)
                ->setTitle(self::PAYMENT_TITLE)
                ->setHandlerClass($this->getName());
        }

        return $paymentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentResponseForUser($user, $paymentType)
    {
        return new RedirectResponse($this->getRouter()->generate(
                'evp_send_invoice',
                array(
                    'token' => $user->getOrder()->getToken(),
                    'type' => self::PROFORMA_INVOICE,
                ),
                true
            )
        );
    }
}
