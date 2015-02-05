<?php
namespace Evp\Bundle\DeviceApiBundle\Controller;

use Evp\Bundle\DeviceApiBundle\Entity\ApiTicketStatus;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Evp\Bundle\DeviceApiBundle\Entity\ApiTicket;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ApiRestController
 * @package Evp\Bundle\DeviceApiBundle\Controller
 */
class ApiRestController extends FOSRestController
{

    /**
     * Gets all Tickets for offline ticket check
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTicketsSyncAction(Request $request)
    {
        $this->get('logger')->debug('New api call',
            array(
                'method' => __METHOD__,
                'data' => func_get_args()
            )
        );

        $filters = $request->query;
        $syncManager = $this->get('evp_device_api.offline_sync_manager');
        $response = $syncManager
            ->prepareOfflineData($filters)
            ->buildView()
            ->getResponse();

        return $response;
    }

    /**
     * Syncs used tickets from remote device
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function patchTicketsSyncAction(Request $request)
    {
        $this->get('logger')->debug('New api call',
            array(
                'method' => __METHOD__,
                'data' => func_get_args()
            )
        );

        $ticketExaminer = $this->get('security.context')->getToken()->getUser();
        $filters = $request->query;
        $used = $request->request->get('used_tickets');
        if (!empty($used)) {
            $syncManager = $this->get('evp_device_api.offline_sync_manager')
                ->setExaminer($ticketExaminer)
                ->markUsedTickets($used);
            $status = $syncManager->getStatusCode();
            $response = $syncManager
                ->prepareOfflineData($filters)
                ->buildView()
                ->getResponse();
            $response->setStatusCode($status);
            return $response;
        } else {
            return new Response(null, 400, array('Content-Type' => 'text/plain'));
        }
    }

    /**
     * Get action for a single ticket
     *
     * @param $base64EncodedTicketUrl
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTicketAction($base64EncodedTicketUrl)
    {
        $this->get('logger')->debug('New api call',
            array(
                'method' => __METHOD__,
                'data' => func_get_args()
            )
        );

        $ticket = $this->getTicketFromBase64EncodedUrl($base64EncodedTicketUrl);
        $apiTicket = ApiTicket::createFromTicket($ticket);
        $apiTicket->setCodeContents(
            $this->generateUrl(
                'ticket_checker',
                array('ticketToken' => $ticket->getToken()),
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );

        $view = $this->buildJsonViewFromObject($apiTicket);
        return $this->handleView($view);
    }

    /**
     * Update a ticket so that it would become used
     *
     * @param string $base64EncodedTicketUrl
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function patchTicketAction($base64EncodedTicketUrl)
    {
        $this->get('logger')->debug('New api call',
            array(
                'method' => __METHOD__,
                'data' => func_get_args()
            )
        );

        $ticketExaminer = $this->get('security.context')->getToken()->getUser();

        $ticket = $this->getTicketFromBase64EncodedUrl($base64EncodedTicketUrl);
        $message = null;

        if ($ticket->getStatus() === Ticket::STATUS_UNUSED) {
            $this->get('evp.service.ticket_manager')->markAsUsed($ticket, $ticketExaminer);
            $message = 'ticket has been used';
        } else {
            $message = 'ticket already used';
        }

        $apiTicket = ApiTicketStatus::createFromTicket($ticket);
        $apiTicket->setMessage($message);

        $view = $this->buildJsonViewFromObject($apiTicket);
        return $this->handleView($view);
    }

    /**
     * @param $base64EncodedTicketUrl
     * @return \Evp\Bundle\TicketBundle\Entity\Ticket
     */
    private function getTicketFromBase64EncodedUrl($base64EncodedTicketUrl)
    {
        $ticketToken = $this->get('evp_device_api.ticket_token_parser')
            ->parseFromBase64Url($base64EncodedTicketUrl);

        $ticket = $this->getDoctrine()
            ->getRepository('EvpTicketBundle:Ticket')
            ->findOneBy(
                array('token' => $ticketToken)
            );
        return $ticket;
    }

    /**
     * Builds view from entity
     *
     * @param object $entity
     * @return View
     */
    private function buildJsonViewFromObject($entity)
    {
        $view = View::create();
        $view->setStatusCode(200)
            ->setFormat('json')
            ->setData($entity);
        return $view;
    }
} 