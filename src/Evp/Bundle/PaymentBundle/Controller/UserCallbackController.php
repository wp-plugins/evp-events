<?php

namespace Evp\Bundle\PaymentBundle\Controller;

use Evp\Bundle\TicketBundle\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class UserCallbackController extends Controller
{
    public function paymentCompletedOrPendingAction($orderToken)
    {
        return $this->render(
            'EvpPaymentBundle:UserCallback:paymentCompletedOrPending.html.twig',
            array(
                'orderToken' => $orderToken
            )
        );
    }

    public function paymentCancelledAction()
    {
        return $this->render(
            'EvpPaymentBundle:UserCallback:paymentCancelled.html.twig'
        );
    }

    public function ajaxCheckOrderAction($orderToken) {

        $orderRepository = $this->getDoctrine()->getRepository('EvpTicketBundle:Order');
        $order = $orderRepository->getOrderByToken($orderToken);

        $orderManager = $this->get('evp.service.order_manager');
        $ticketsAvailable = array();

        if ($orderManager->isOrderDone($order)) {
            $ticketRepository = $this->getDoctrine()->getRepository('EvpTicketBundle:Ticket');
            $ticketsAvailable = $ticketRepository->getAllByOrder($order);
        }

        $responseHash = array(
            'order' => $order,
            'tickets' => $ticketsAvailable
        );

        return $this->render('@EvpPayment/UserCallback/checkOrderAction.ajax.twig',
            array(
                'responseHash' => $responseHash
            )
        );
    }

}
