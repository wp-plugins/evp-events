<?php
/**
 * BlankAction. Does nothing
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;

/**
 * Class BlankAction
 */
class BlankAction extends ActionAbstract {

    /**
     * @var string
     */
    protected $actionName = 'index';

    /**
     * @var string
     */
    protected $responseType = ActionInterface::RESPONSE_REGULAR;

    /**
     * Sets Parameters for Action
     *
     * @param array $params
     * @return self
     */
    public function setParameters($params) {
        return $this;
    }

    /**
     * Builds template parameters by FQCN
     *
     * @return array
     */
    public function buildResponseParameters() {
        return array(
            'columns' => array(),
            'filters' => array(),
            'records' => array(),
        );
    }
}
