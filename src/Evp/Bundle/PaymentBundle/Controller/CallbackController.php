<?php

namespace Evp\Bundle\PaymentBundle\Controller;

use Evp\Bundle\TicketBundle\Service\MailManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Evp\Bundle\TicketBundle\Entity as TicketEntities;
use Evp\Bundle\TicketBundle\Service\MailStrategy\StrategyInterface;

class CallbackController extends Controller
{
    public function handleCallbackAction($paymentHandlerName)
    {
        $paymentHandler = $this->get('evp_payment.service.payment_handler_provider')
            ->getHandlerFromName($paymentHandlerName);

        $order = $paymentHandler->handleCallback($this->getRequest());
        if(!$order) {
            return $paymentHandler->getErrorResponse();
        }

        $this->get('evp.service.order_manager')
            ->updateTicketStatus($order)
            ->updateOrderStatus($order)
            ->updateSeatStatus($order)
            ->updateInvoice($order);

        try {
            $this->sendEmailsWithTickets($order);
            $this->sendEmailWithInvoice($order);
        } catch (\Exception $e) {
            $this->get('logger')->error('Failed to send Emails', array($e));
        }

        return $paymentHandler->getOkResponse();
    }

    /**
     * @param TicketEntities\Order $order
     */
    private function sendEmailsWithTickets($order)
    {
        $this->get('evp.service.mail_manager')
            ->prepareMessage('multipleTickets', $order->getToken())
            ->sendMessage()
            ->getResult();
    }

    /**
     * Checks if Invoice data are present for Order, validate & send
     *
     * @param TicketEntities\Order $order
     */
    private function sendEmailWithInvoice(TicketEntities\Order $order) {
         if(
            $this->get('evp.service.order_manager')
                ->isOrderValidForInvoice($order->getToken())
         ) {
             $this
                 ->get('evp.service.mail_manager')
                 ->setTemplateType(StrategyInterface::INVOICE_FINAL)
                 ->prepareMessage(MailManager::MAIL_TYPE_INVOICE_FINAL, $order->getToken())
                 ->sendMessage()
                 ->getResult();
         }
    }
}
