<?php
/**
 * Resends Final invoice for given Order
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\OrderMenu;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;
use Evp\Bundle\TicketBundle\Service\MailStrategy\StrategyInterface;

/**
 * Class ResendInvoiceFinalAction
 */
class ResendInvoiceFinalAction extends ActionAbstract implements ActionInterface {

    const ROUTER_NAME = 'evp_send_invoice';

    /**
     * @var string
     */
    protected $actionName = 'resend-invoice_final';

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_REDIRECT;

    /**
     * Gets the current Action Template for Twig
     *
     * @return string
     */
    public function getResponseName() {
        return self::ROUTER_NAME;
    }

    /**
     * Sets Parameters for Action
     *
     * @param array $params
     * @return self
     */
    public function setParameters($params) {
        $this->fqcn = $params['fqcn'];
        return $this;
    }

    /**
     * Builds template parameters by FQCN
     *
     * @return array
     */
    public function buildResponseParameters() {
        $this->logger->debug('Got User request for action \'' .$this->actionName .'\'');
        $order = $this->entityManager->getRepository($this->fqcn)
            ->findOneBy(array('id' => $this->targetId));
        return array(
            'token' => $order->getToken(),
            'type' => StrategyInterface::INVOICE_FINAL,
        );
    }
}
