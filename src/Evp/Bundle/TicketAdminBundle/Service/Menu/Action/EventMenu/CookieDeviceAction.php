<?php
/**
 * CookieDevice action for attaching device using Cookie
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\EventMenu;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;

/**
 * Class CookieDeviceAction
 */
class CookieDeviceAction extends ActionAbstract implements ActionInterface {

    const TEMPLATE_NAME = 'EvpTicketAdminBundle:Device:attachDevice.html.twig';
    const EVENT_FQCN = 'Evp\Bundle\TicketBundle\Entity\Event';

    /**
     * @var string
     */
    protected $actionName = 'cookie_device';

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_REGULAR;

    /**
     * Gets the current Action Template for Twig
     *
     * @return string
     */
    public function getResponseName() {
        return self::TEMPLATE_NAME;
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
        $event = $this->entityManager->getRepository(self::EVENT_FQCN)
            ->findOneBy(array('id' => $this->parent['id']));
        return array(
            'columns' => $event->getToken(),
        );
    }
}
