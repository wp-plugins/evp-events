<?php

namespace Evp\Bundle\TicketBundle\Controller;

use Evp\Bundle\TicketBundle\Service\TicketCodeStrategy\TicketCodeStrategyInterface;
use Evp\Bundle\TicketMaintenanceBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\Response;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Is used to show/generate QR code images
 *
 * Class QrCodeController
 * @package Evp\Bundle\TicketBundle\Controller
 */
class QrCodeController extends RedirectController
{
    /**
     * @param $ticketToken
     * @return Response
     */
    public function showAction($ticketToken)
    {
        /** @var Ticket $ticket */

        $codeGenerator = $this->get('evp.service.ticket_code_generator');

        $ticket = $this->getDoctrine()
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->findOneBy(array(
                    'token' => $ticketToken
                ));
        $imageContent = $codeGenerator->createFromTicket($ticket);
        return $this->renderAsAnImage($imageContent);
    }

    /**
     * Generates the QR code for device attachment to Event by Event token
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pairingCodeAction($token) {
        $qrGenerator = $this->get('evp.service.ticket_code_generator')
            ->getStrategy(TicketCodeStrategyInterface::STRATEGY_QR_CODE);
        $event = $this->getDoctrine()
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Event')
            ->findOneBy(array(
                    'token' => $token,
                ));
        $imageContent = $qrGenerator->createFromEvent($event);
        return $this->renderAsAnImage($imageContent);
    }

    /**
     * Creates a QR code, that has TicketExaminer information coded in it
     *
     * @param string $token
     * @return Response
     */
    public function generateQrForApiDeviceAction($token)
    {
        $ticketExaminer = $this->getDoctrine()
            ->getRepository('Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer')
            ->findOneBy(
                array('token' => $token)
            );

        if (!$ticketExaminer) {
            return $this->appendNoPartials(
                new Response('Invalid token detected')
            );
        }

        $qrGenerator = $this->get('evp.service.ticket_code_generator')
            ->getStrategy(TicketCodeStrategyInterface::STRATEGY_QR_CODE);
        $serializer = $this->get('jms_serializer');

        $ticketExaminer->setApiUrl($this->generateUrl('api_route_base', array(), UrlGeneratorInterface::ABSOLUTE_URL));
        $jsonContent = $serializer->serialize($ticketExaminer, 'json');

        $imageContent = $qrGenerator->createFromString($jsonContent);
        return $this->renderAsAnImage($imageContent);
    }

    /**
     * Returns the content as a response with image mime type
     *
     * @param string $content
     * @return Response
     */
    protected function renderAsAnImage($content)
    {
        return new Response(
          $content,
          200,
          array(
              'Content-Type' => 'image/png'
          )
        );
    }
}
