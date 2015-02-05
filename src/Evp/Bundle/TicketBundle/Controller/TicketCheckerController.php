<?php
namespace Evp\Bundle\TicketBundle\Controller;

use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketBundle\Service\TwigTemplateManager;
use Evp\Bundle\TicketMaintenanceBundle\Controller\RedirectController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TicketCheckerController extends RedirectController
{
    public function checkTicketAction($ticketToken)
    {
        $ticketRepo = $this->getDoctrine()->getRepository('EvpTicketBundle:Ticket');
        $ticket = $ticketRepo->findOneBy(
                array(
                    'token' => $ticketToken
                )
            );

        if (!empty($ticket)) {
            $ticketCheckAllowed = $this->get('evp.service.ticket_device_checker')
                ->isAuthorizedToCheckTicket(
                    $ticket,
                    $this->getRequest()
                );
            if ($ticketCheckAllowed) {
                $twigDbLoader = $this->get('evp.service.database_twig_loader');
                $twig = $twigDbLoader
                    ->setObject($ticket->getEvent())
                    ->getTwig();

                $templateName = 'ticket_' .$ticket->getStatus() .'.html.twig';
                $response = new Response($twig->render($templateName, array('ticket' => $ticket)));

                if ($ticket->getStatus() === Ticket::STATUS_UNUSED) {
                    $ticket->setStatus(Ticket::STATUS_USED);
                    $this->getDoctrine()->getManager()->flush($ticket);
                }

                $response->headers->set('Content-Type', 'text/html');
                return $this->appendNoPartials($response);
            }
        }

        return $this->render('EvpTicketBundle:TicketChecker:checkTicketError.html.twig');
    }
}
