<?php
/**
 * PrintController for Print requests by type & token to HTML or PDF
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Controller;

use Evp\Bundle\TicketBundle\Service\HtmlConvert\HtmlConvertInterface;
use Evp\Bundle\TicketBundle\Service\TwigTemplateManager;
use Evp\Bundle\TicketMaintenanceBundle\Controller\RedirectController;
use Evp\Bundle\TicketBundle\Service\MailStrategy\StrategyInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PrintController
 */
class PrintController extends RedirectController {

    /**
     * Returns Ready-to-print HTML or PDF Response by Ticket token
     * @param string $token
     * @param string $output
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ticketAction($token, $output = HtmlConvertInterface::OUTPUT_PDF) {
        if ($output == HtmlConvertInterface::OUTPUT_PDF) {
            $output = $this->get('evp.service.html_convert.current_converter')->getOutputFormat();
        }

        $ticketManager = $this->get('evp.service.ticket_manager');
        if (!$ticketManager->validateTicket($token)) {
            return $this->redirect($this->generateUrl('display_error', array('message' => 'error.ticket.not_valid')));
        }
        $ticket = $this->getDoctrine()->getManager()->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->findOneBy(
                array(
                    'token' => $token,
                )
            );
        $twigDbLoader = $this->get('evp.service.database_twig_loader');
        $twig = $twigDbLoader
            ->setObject($ticket)
            ->setType(TwigTemplateManager::TYPE_TICKET)
            ->getTwig();

        $ticketFieldRecords = $ticketManager->getTicketFieldRecords($ticket);
        $orderRecords = $this->get('evp.service.order_manager')->getOrderRecordsByUser($ticket->getUser());

        $html = $twig->render($ticketManager->getPrintTemplate(), array(
                'ticket' => $ticket,
                'fieldRecords' => $ticketFieldRecords,
                'orderRecords' => $orderRecords,
            )
        );

        if ($output === HtmlConvertInterface::OUTPUT_HTML) {
            return new Response($html,
                200,
                array(
                    'Content-Type' => 'text/html;charset=utf-8',
                    'no-partials' => 1
                )
            );
        }
        if ($output === HtmlConvertInterface::OUTPUT_PDF) {
            $responseUrl = $this->generateUrl(
                'evp_print_ticket',
                array(
                    'token' => $token,
                    'output' => 'html',
                ),
                true
            );
            $pdf = $this->get('evp.service.html_convert.current_converter')->renderUrl($responseUrl);
            $fileName = $ticket->getEvent()->getName() . $this->getUniqueSuffix();
            return new Response(
                $pdf,
                200,
                array(
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment;filename="'.$fileName.'.pdf"',
                )
            );
        }
    }

    /**
     * Returns Ready-to-print HTML or PDF Response by Ticket token
     * @param string $token
     * @param string $output
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ticketsAction($token, $output = HtmlConvertInterface::OUTPUT_PDF) {
        if ($output == HtmlConvertInterface::OUTPUT_PDF) {
            $output = $this->get('evp.service.html_convert.current_converter')->getOutputFormat();
        }

        $order = $this->getDoctrine()->getRepository('Evp\Bundle\TicketBundle\Entity\Order')
            ->findOneBy(
                array(
                    'token' => $token,
                )
            );

        $files = array();
        foreach ($order->getUser()->getTickets() as $ticket) {
            $response = $this->ticketAction($ticket->getToken(), $output);
            $fileName = sys_get_temp_dir() .'/' . $ticket->getEvent()->getName() . $this->getUniqueSuffix() . '.pdf';
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            file_put_contents($fileName, $response->getContent());
            $files[$ticket->getEvent()->getName() . $this->getUniqueSuffix() . '.pdf'] = $fileName;
        }
        $zipFile = tempnam(sys_get_temp_dir(), 'zip'). '.zip';

        $zip = new \ZipArchive;
        $zip->open($zipFile, \ZipArchive::OVERWRITE);
        foreach ($files as $local => $file) {
            $zip->addFile($file, $local);
        }
        $zip->close();

        return new Response(
            file_get_contents($zipFile),
            200,
            array(
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment;filename="'.$order->getEvent()->getName().'.zip"',
            )
        );
    }

    /**
     * Returns Ready-to-print HTML or PDF Response by Order token
     *
     * @param string $token
     * @param string $output
     * @param string $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function invoiceAction($token, $output = HtmlConvertInterface::OUTPUT_PDF, $type) {
        if ($output == HtmlConvertInterface::OUTPUT_PDF) {
            $output = $this->get('evp.service.html_convert.current_converter')->getOutputFormat();
        }
        $orderManager = $this->get('evp.service.order_manager');
        if (!$orderManager->isOrderValidForInvoice($token)) {
            return $this->redirect($this->generateUrl('display_error', array('message' => 'error.order.not_valid')));
        }
        $order = $this->getDoctrine()->getManager()->getRepository('Evp\Bundle\TicketBundle\Entity\Order')
            ->findOneBy(
                array(
                    'token' => $token,
                )
            );
        $orderDetails = $this->getDoctrine()->getManager()->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findBy(
                array(
                    'order' => $order,
                )
            );

        $twigDbLoader = $this->get('evp.service.database_twig_loader');

        $templateType = null;
        if ($type == StrategyInterface::INVOICE_FINAL) {
            $templateType = TwigTemplateManager::TYPE_INVOICE_FINAL;
        }
        if ($type == StrategyInterface::INVOICE_PROFORMA) {
            $templateType = TwigTemplateManager::TYPE_INVOICE_PROFORMA;
        }

        $twig = $twigDbLoader
            ->setObject($order->getEvent())
            ->setType($templateType)
            ->getTwig();

        $html = $twig->render($orderManager->getPrintTemplate($templateType), array(
                'invoice' => $order->getInvoice(),
                'order' => $order,
                'orderDetails' => $orderDetails,
            )
        );

        if ($output === HtmlConvertInterface::OUTPUT_HTML) {
            return new Response($html,
                200,
                array(
                    'Content-Type' => 'text/html;charset=utf-8',
                    'no-partials' => 1,
                )
            );
        }
        if ($output === HtmlConvertInterface::OUTPUT_PDF) {
            $responseUrl = $this->generateUrl(
                'evp_print_invoice',
                array(
                    'token' => $token,
                    'output' => 'html',
                    'type' => $type,
                ),
                true
            );
            $pdf = $this->get('evp.service.html_convert.current_converter')->renderUrl($responseUrl);
            $fileName = $order->getInvoice()->getId();
            return new Response(
                $pdf,
                200,
                array(
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment;filename="'.$fileName.'.pdf"',
                    'no-partials' => 1,
                )
            );
        }
    }

    /**
     * @return string
     */
    protected function getUniqueSuffix()
    {
        $now = new \DateTime();
        return '_' . $now->format('YmdHis') . '_' . mt_rand(1000, 9999);
    }
}
