<?php
/**
 * Link action for getting link for Event
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\EventMenu;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;

/**
 * Class LinkAction
 */
class LinkAction extends ActionAbstract implements ActionInterface {

    const ROUTER_NAME = 'display_info_no_partials';
    const ROUTER_EVENT_LINK_GENERATOR = 'evp_ticket_event_info';

    /**
     * @var string
     */
    protected $actionName = 'link';

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
        $event = $this->entityManager->getRepository($this->fqcn)
            ->findOneBy(array('id' => $this->targetId));
        $url = $this->router->generate(
            self::ROUTER_EVENT_LINK_GENERATOR,
            array(
                '_locale' => $event->getDefaultLocale(),
                'eventId' => $event->getId(),
            ),
            true
        );
        return array(
            'message' => base64_encode($url),
        );
    }
}
