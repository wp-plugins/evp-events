<?php
/**
 * MailController for sending mail by type and token
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Controller;


use Evp\Bundle\TicketBundle\Service\MailManager;
use Evp\Bundle\TicketBundle\Service\MailStrategy\StrategyInterface;
use Evp\Bundle\TicketMaintenanceBundle\Controller\RedirectController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MailController
 */
class MailController extends Controller {

    /**
     * Sends Invoice to assigned User
     *
     * @param string $token
     * @param string $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function invoiceAction($token, $type) {
        $invoiceType = null;
        if ($type == StrategyInterface::INVOICE_PROFORMA) {
            $invoiceType = MailManager::MAIL_TYPE_INVOICE_PROFORMA;
        }
        if ($type == StrategyInterface::INVOICE_FINAL) {
            $invoiceType = MailManager::MAIL_TYPE_INVOICE_FINAL;
        }

        $result = $this
            ->get('evp.service.mail_manager')
            ->setTemplateType($type)
            ->prepareMessage($invoiceType, $token)
            ->sendMessage()
            ->getResult();
        if ($result) {
            return $this->forward(
                'EvpTicketBundle:GenericDisplay:displayInfo',
                array('message' => 'message.invoice.sent_successfully')
            );
        }
        return $this->forward(
            'EvpTicketBundle:GenericDisplay:displayError',
            array('message' => 'message.invoice.failed_to_send')
        );
    }

    /**
     * Sends Ticket to assigned User
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ticketAction($token) {
        $result = $this
            ->get('evp.service.mail_manager')
            ->prepareMessage(MailManager::MAIL_TYPE_TICKET, $token)
            ->sendMessage()
            ->getResult();
        if ($result) {
            return $this->forward(
                'EvpTicketBundle:GenericDisplay:displayInfo',
                array('message' => 'message.ticket.sent_successfully')
            );
        }
        return $this->forward(
            'EvpTicketBundle:GenericDisplay:displayError',
            array('message' => 'message.ticket.failed_to_send')
        );
    }

    /**
     * Sends All Tickets by given Order Token to assigned User
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ticketsAction($token) {
        $result = $this
            ->get('evp.service.mail_manager')
            ->prepareMessage(MailManager::MAIL_TYPE_MULTIPLE_TICKETS, $token)
            ->sendMessage()
            ->getResult();
        if ($result) {
            return $this->forward(
                'EvpTicketBundle:GenericDisplay:displayInfo',
                array('message' => 'message.tickets.sent_successfully')
            );
        }
        return $this->forward(
            'EvpTicketBundle:GenericDisplay:displayError',
            array('message' => 'message.tickets.failed_to_send')
        );
    }
}
