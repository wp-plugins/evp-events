<?php
/**
 * User-side Ajax controller
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Controller;

use Evp\Bundle\TicketBundle\Service\OrderManager;
use Evp\Bundle\TicketBundle\Service\SeatManager;
use Evp\Bundle\TicketMaintenanceBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AjaxController
 * @package Evp\Bundle\TicketBundle\Controller
 */
class AjaxController extends RedirectController {

    /**
     * Tries to reserve current Seat for current User
     *
     * @param string $seatId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reserveSeatAction($seatId) {
        $seatManager = $this->get('evp.service.seat_manager');
        $userSession = $this->get('evp.service.user_session');

        $result = $seatManager
            ->setUser($userSession->getUserForThisSession())
            ->setSeatId($seatId)
            ->reserveSeat();

        $requestedOrderDetails = null;
        if ($result === SeatManager::RESULT_OK) {
            $event = $this->getDoctrine()->getManager()->find('EvpTicketBundle:Event',
                $this->getRequest()->getSession()->get($this->container->getParameter('evp.service.event_id_session_key')));

            $requestedOrderDetails = $this->getDoctrine()
                ->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
                ->getAllByUserAndEvent(
                    $userSession->getUserForThisSession(),
                    $event
                );
        }
        $cart = $this->renderView(
            'EvpTicketBundle:Step:TicketTypeSelection/requestedTicketTypes.html.twig',
            array(
                'elements' => array(
                    'requested' => $requestedOrderDetails,
                )
            )
        );

        return $this->appendNoPartials(new Response($cart, $seatManager->getStatusCode()));
    }

    /**
     * Modifies (creates or updates) current OrderDetail
     *
     * @param int $ticketTypeId
     * @param int $count
     *
     * @return Response
     */
    public function modifyOrderDetailsAction($ticketTypeId, $count)
    {
        $orderManager = $this->get('evp.service.order_manager');
        $currentUser = $this->get('evp.service.user_session')->getUserForThisSession();
        $ticketType = $this->getDoctrine()->getRepository('EvpTicketBundle:TicketType')
            ->findOneBy(array(
                    'id' => $ticketTypeId,
                ));
        if (empty($ticketType)) {
            return $this->appendNoPartials(new Response('Requested TicketType not found', 404));
        }

        $result = $orderManager->modifyOrderDetailsCountForTicketType($ticketType, (int)$count);
        if ($result != OrderManager::STATUS_ORDER_DETAILS_OK) {
            return $this->appendNoPartials(
                new Response(
                    $this->get('translator')->trans($result, array(), 'validators'),
                    409
                )
            );
        }
        $orderDetails = $this->getDoctrine()->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findOneBy(array(
                    'ticketType' => $ticketType,
                    'user' => $currentUser,
                ));
        $this->get('evp.service.ticket_manager')->handleTicketsDifferenceByOrderDetails($orderDetails);
        $jsonResponse = $this->get('evp.service.json_data_manager')->getJsonForOrderDetailsAjaxUpdate($orderDetails);

        return $this->appendNoPartials(new Response($jsonResponse, 200));
    }
}
