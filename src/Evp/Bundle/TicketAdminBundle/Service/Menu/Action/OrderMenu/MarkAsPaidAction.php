<?php
/**
 * MarkAsPaid action for manual Order payment confirmation
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\OrderMenu;

use Evp\Bundle\PaymentBundle\PaymentHandler\InvoiceHandler;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;
use Evp\Bundle\TicketBundle\Service\MailManager;
use Evp\Bundle\TicketBundle\Service\MailStrategy\StrategyInterface;

/**
 * Class MarkAsPaidAction
 */
class MarkAsPaidAction extends ActionAbstract implements ActionInterface {

    /**
     * @var string
     */
    protected $actionName = 'confirm';

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_REDIRECT;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\OrderManager
     */
    private $orderManager;

    /**
     * @var MailManager
     */
    private $mailManager;

    /**
     * Managers collection
     *
     * @param array $managers
     */
    public function setManagers($managers) {
        $this->orderManager = $managers['orderManager'];
        $this->mailManager = $managers['mailManager'];
    }

    /**
     * Builds template parameters by FQCN
     *
     * @return array
     */
    public function buildResponseParameters() {
        $this->logger->debug('Got User request for action \'' .$this->actionName .'\'');
        $managed = $this->manageStatus();
        if ($managed) {
            $this->sendTickets();
        }
        return array(
            'menu' => $this->shortClassName,
        );
    }

    /**
     * Manages the Status codes in Order & Tickets
     *
     * @return bool
     */
    private function manageStatus()
    {
        $order = $this->entityManager->getRepository($this->fqcn)
            ->findOneBy(array('id' => $this->targetId));

        try {
            $this->orderManager
                ->updateTicketStatus($order)
                ->updateOrderStatus($order);
            if ($order->getInvoiceRequired() == true || $order->getPaymentType() == InvoiceHandler::PAYMENT_NAME) {
                $this->mailManager
                    ->setTemplateType(StrategyInterface::INVOICE_FINAL)
                    ->prepareMessage(MailManager::MAIL_TYPE_INVOICE_FINAL, $order->getToken())
                    ->sendMessage();
            }
            $status = true;
        } catch (\Exception $e) {
            $this->logger->debug('Failed to update order status', array($e));
            $status = false;
        }
        return $status;
    }

    /**
     * Sends the Tickets by current Order
     */
    private function sendTickets()
    {
        $order = $this->entityManager->getRepository($this->fqcn)
            ->findOneBy(array('id' => $this->targetId));

        $this->logger->debug('Trying to send Tickets for order', array($order));

        $result = $this->mailManager
            ->prepareMessage(MailManager::MAIL_TYPE_MULTIPLE_TICKETS, $order->getToken())
            ->sendMessage()
            ->getResult();
        $this->logger->debug('Tickets sent with result', array($result));
    }
}
