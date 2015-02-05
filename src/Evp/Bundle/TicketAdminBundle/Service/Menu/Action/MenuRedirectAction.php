<?php
/**
 * MenuRedirect action to redirect to specific menu with optional params
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action;

/**
 * Class MenuRedirectAction
 */
class MenuRedirectAction extends ActionAbstract implements ActionInterface {

    const ROUTER_NAME = 'admin_manage_action';

    /**
     * @var string
     */
    protected $actionName = 'redirect';

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
        $this->actionName = $params['actionName'];
        return $this;
    }

    /**
     * Builds template parameters by FQCN
     *
     * @return array
     */
    public function buildResponseParameters() {
        $redirectParent = $this->redirectParents[$this->redirects[$this->actionName]];
        if (ucfirst($redirectParent) != $this->shortClassName) {
            $redirectParent = lcfirst($this->shortClassName);
        }
        $parent = array(
            'class' => $redirectParent,
            'id' => $this->targetId,
        );
        $this->setParent($parent);
        return array(
            'menu' => $this->redirects[$this->actionName],
            'action' => 'index',
        );
    }
}
