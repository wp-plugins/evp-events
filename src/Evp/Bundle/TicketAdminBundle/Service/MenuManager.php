<?php
/**
 * MenuManager for dynamic management of Menu items
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service;

use Evp\Bundle\TicketAdminBundle\Service\Menu\MenuInterface;
use Evp\Bundle\TicketBundle\Service\DatabaseTwigLoader;
use Evp\Bundle\TicketMaintenanceBundle\Services\CurrentUserProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

/**
 * Class MenuManager
 */
class MenuManager {

    /**
     * @var \Evp\Bundle\TicketAdminBundle\Service\Menu\MenuInterface[]
     */
    private $menuItems;

    /**
     * @var string[] Twig templates
     */
    private $templates;

    /**
     * @var \Evp\Bundle\TicketAdminBundle\Service\Menu\MenuInterface
     */
    private $currentMenu = null;

    /**
     * @var \Symfony\Bridge\Twig\TwigEngine
     */
    private $twig;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    private $router;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;

    /**
     * @var array
     */
    private $hiddenMenus;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Evp\Bundle\TicketMaintenanceBundle\Services\CurrentUserProvider
     */
    private $userProvider;

    /**
     * Sets required parameters
     *
     * @param string[]                                                        $templates
     * @param \Evp\Bundle\TicketBundle\Service\DatabaseTwigLoader             $twigDbLoader
     * @param \Symfony\Component\Routing\Router                               $router
     * @param \Symfony\Component\HttpFoundation\Session\Session               $session
     * @param array                                                           $supplemental
     * @param \Psr\Log\LoggerInterface                                        $log
     * @param CurrentUserProvider                                             $userProvider
     */
    public function __construct(
        $templates,
        DatabaseTwigLoader $twigDbLoader,
        Router $router,
        Session $session,
        $supplemental,
        LoggerInterface $log,
        CurrentUserProvider $userProvider
    ) {
        $this->templates = $templates;
        $this->twig = $twigDbLoader->getTwig();
        $this->router = $router;
        $this->session = $session;
        $this->hiddenMenus = $supplemental['hidden_menus'];
        $this->logger = $log;
        $this->userProvider = $userProvider;
    }

    /**
     * Adds the tagged Menu to collection
     *
     * @param \Evp\Bundle\TicketAdminBundle\Service\Menu\MenuInterface $menuItem
     * @param string $alias
     */
    public function addMenuItem(MenuInterface $menuItem, $alias) {
        $this->menuItems[$alias] = $menuItem;
    }

    /**
     * Gets the assoc. array of all Menu items in singular or plural
     *
     * @param bool $singular
     * @return array
     */
    public function getTranslatedMenuList($singular = true) {
        $menuList = array();
        foreach ($this->menuItems as $name => $item) {
            if (!in_array($name, $this->hiddenMenus)) {
                $menuList[$name] = $item->getMenuName($singular);
            }
        }
        return $menuList;
    }

    /**
     * Gets regular template for given action name
     *
     * @param string $action
     * @return string
     */
    public function getRegularTemplate($action) {
        return $this->templates[$action];
    }

    /**
     * Sets current Menu by its Alias
     *
     * @param string $alias
     * @return self
     */
    public function setMenuItem($alias) {
        $this->logger->debug(
            'Got current User activity on menu ' .$alias,
            $this->userProvider->getCurrentUserData()
        );
        $this->currentMenu = $this->menuItems[$alias];
        return $this;
    }

    /**
     * Sets the Parent class & id for particular action
     *
     * @param array $parent
     * @return self
     */
    public function setParent($parent) {
        if ($parent['class'] != '' and $parent['id'] != '') {
            $this->session->set('parent', $parent);
        }
        return $this;
    }

    /**
     * Sets current Menu action
     *
     * @param string $action
     * @return self
     */
    public function setAction($action) {
        $this->logger->debug(
            'Got current User activity on menu action ' .$action,
            $this->userProvider->getCurrentUserData()
        );
        $this->currentMenu->setCurrentAction($action);
        $currentAction = $this->currentMenu->getCurrentAction();
        if (!empty($currentAction)) {
            $currentAction
                ->setShortClassName(
                    $this->currentMenu->getShortClassName()
                )
                ->setRequest($this->request);
        }
        return $this;
    }

    /**
     * Sets the Request for currentMenu
     *
     * @param Request $request
     * @return self
     */
    public function setRequest(Request $request) {
        $this->currentMenu->setRequest($request);
        $this->request = $request;
        return $this;
    }

    /**
     * Sets the target Id on which specified Action will be taken
     *
     * @param string $id
     * @return $this
     */
    public function setTarget($id = null) {
        $this->currentMenu->setTarget($id);
        return $this;
    }

    /**
     * Gets the Template name for currentMenu
     *
     * @return string
     */
    public function getResponseName() {
        return $this->currentMenu->getResponseName();
    }

    /**
     * Gets the currentMenu
     *
     * @return MenuInterface
     */
    public function getCurrentMenu() {
        return $this->currentMenu;
    }

    /**
     * Gets the Template parameters for currentMenu
     *
     * @return array
     */
    public function getResponseParameters() {
        return $this->currentMenu->getResponseParameters();
    }

    /**
     * Gets the Response type for currentMenu currentAction
     *
     * @return string
     */
    private function getActionResponseType() {
        return $this->currentMenu->getActionResponseType();
    }

    /**
     * Gets the Menu sub-items for specific menu item
     *
     * @return array
     */
    private function getSpecificMenuItems() {
        return $this->currentMenu->getSpecificMenuItems();
    }

    /**
     * Returns the Response for currentMenu in given Action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse() {
        $menus = array(
            'general' => $this->getTranslatedMenuList(false),
            'specific' => $this->getSpecificMenuItems(),
        );
        if ($this->getActionResponseType() === ActionInterface::RESPONSE_REGULAR) {
            $view = $this->twig->render(
                $this->getResponseName(),
                array_merge($this->getResponseParameters(), array('menus' =>$menus))
            );
            return new Response($view);
        }
        if ($this->getActionResponseType() === ActionInterface::RESPONSE_REDIRECT) {
            return new RedirectResponse(
                $this->router->generate(
                    $this->getResponseName(),
                    $this->getResponseParameters()
                ),
                302,
                array('no-partials' => 1)
            );
        }
    }
}
