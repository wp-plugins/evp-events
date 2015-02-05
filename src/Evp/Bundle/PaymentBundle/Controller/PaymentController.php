<?php

namespace Evp\Bundle\PaymentBundle\Controller;

use Evp\Bundle\PaymentBundle\Entity\PaymentType;
use Evp\Bundle\TicketMaintenanceBundle\Controller\RedirectController;

class PaymentController extends RedirectController
{
    public function redirectToPaymentAction() {

        $currentUser = $this->get('evp.service.user_session')->getUserForThisSession();
        $paymentTypeName = $currentUser->getOrder()->getPaymentType();
        $paymentType = PaymentType::create()
            ->setName($paymentTypeName);

        $paymentHandler = $this->get('evp_payment.service.payment_handler_provider')->getHandlerForPaymentTypeAndUser(
            $paymentType,
            $currentUser
        );
        if ($paymentHandler === null) {
            $this->get('evp.service.user_session')->destroyCurrentSession();

            return $this->redirect(
                $this->generateUrl(
                    'display_error',
                    array('message' => $this->get('translator')->trans('payment.error.no_suitable_payment_handler_found')))
            );
        }

        $response = $paymentHandler->getPaymentResponseForUser(
            $currentUser,
            $paymentType
        );

        $this->get('logger')->debug(
            'Payment response information',
            array(
                $response->getTargetUrl(),
                $response->getContent(),
            )
        );
        $this->get('evp.service.user_session')->destroyCurrentSession();
        return $response;
    }
}
