<?php
/**
 * Payment for multi-step forms
 * Checks if Invoice fields needs to be filled, displays form or redirects
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\PaymentBundle\Service\PaymentHandlerProvider;
use Evp\Bundle\TicketBundle\Entity\User;
use Monolog\Logger;

/**
 * Class PaymentManager
 */
class PaymentManager extends ManagerAbstract {

    /**
     * @var \Evp\Bundle\PaymentBundle\Service\PaymentHandlerProvider
     */
    private $paymentProvider;

    /**
     * @param EntityManager $entityManager
     * @param Logger $logger
     * @param PaymentHandlerProvider $paymentProvider
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        PaymentHandlerProvider $paymentProvider
    ) {
        parent::__construct($entityManager, $logger);
        $this->paymentProvider = $paymentProvider;
    }

    /**
     * Checks if Invoice fill form is required
     * 
     * @param User $user
     * @return bool
     */
    public function isInvoiceRequired(User $user) {
        $required = false;
        if ($user->getOrder()->getInvoiceRequired()) {
            $required = true;
        }
        if ($user->getOrder()->getPaymentType() === 'invoice') {
            $required = true;
        }
        $this->logger->debug('Checking if invoice required for current Order', array($required));
        return $required;
    }
}
