<?php
/**
 * Prints Tickets action for printing tickets by Order token
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\OrderMenu;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;

/**
 * Class PrintTicketsAction
 */
class PrintTicketsAction extends ActionAbstract implements ActionInterface {

    const ROUTER_NAME = 'evp_print_tickets';

    /**
     * @var string
     */
    protected $actionName = 'print-tickets';

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
        );
    }
}
