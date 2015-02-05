<?php
/**
 * RemovalController
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Controller;

use Evp\Bundle\TicketMaintenanceBundle\Controller\RedirectController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class RemovalController
 */
class RemovalController extends RedirectController {

    /**
     * Removes OrderDetails Item & associated Tickets by specified parameters
     *
     * @param string $orderDetailsId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function orderDetailsAction($orderDetailsId) {
        $this->checkValidSession();
        $session = $this->get('session');
        $user = $this->get('evp.service.user_session')->getUserForThisSession();
        $event = $this->getDoctrine()->getManager()
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Event')
            ->findOneBy(array(
                    'id' => $session->get($this->container->getParameter('evp.service.event_id_session_key')),
                )
            );
        $orderDetails = $this->getDoctrine()->getManager()
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findOneBy(array(
                    'id' => $orderDetailsId,
                    'user' => $user,
                    'event' => $event,
                )
            );
        $this->get('evp.service.seat_manager')->freeSeatsByOrderDetails($orderDetails);
        $this->get('evp.service.order_manager')->removeOrderDetailsAndTickets($user, $event, $orderDetails);
        return $this->redirect($this->generateUrl($this->container->getParameter('evp.router.next_step')));
    }

    /**
     * Removes FieldRecords based on ticketId, User, Event
     *
     * @param string $ticketId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ticketRecordsAction($ticketId) {
        $this->checkValidSession();
        $session = $this->get('session');
        $user = $this->get('evp.service.user_session')->getUserForThisSession();
        $event = $this->getDoctrine()->getManager()
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Event')
            ->findOneBy(array(
                    'id' => $session->get($this->container->getParameter('evp.service.event_id_session_key')),
                )
            );
        $ticket = $this->getDoctrine()->getManager()
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->findOneBy(array(
                    'id' => $ticketId,
                )
            );
        $this->get('evp.service.ticket_manager')->removeTicketFieldRecords($user, $event, $ticket);
        return $this->redirect($this->generateUrl($this->container->getParameter('evp.router.next_step')));
    }

    /**
     * Checks if Action is not called accidentally
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function checkValidSession() {
        $session = $this->get('session');

        if (
            !$session->has($this->container->getParameter('evp.service.event_id_session_key'))
            && !$session->has($this->container->getParameter('evp.service.step.current_step_session_key'))
        ) {
            return $this->redirect($this->generateUrl($this->container->getParameter('evp.router.next_step')));
        }
        return true;
    }
}
