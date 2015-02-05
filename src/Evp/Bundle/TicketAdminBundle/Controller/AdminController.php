<?php
/**
 * AdminController v2 for admin actions control
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Controller;

use Evp\Bundle\TicketAdminBundle\Service\Menu\MenuInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Gedmo\Translatable\Entity;
use Evp\Bundle\TicketBundle\Form;

/**
 * Class AdminController
 */
class AdminController extends Controller
{
    /**
     * Renders all Menu items
     *
     * @return Response
     */
    public function dashboardAction() {
        $menuManager = $this->get('evp.ticket_admin.service.menu_manager');

        if ($menuManager->getCurrentMenu() == null) {
            return $this->appendNoPartialsHeader($this->render(
                $menuManager->getRegularTemplate(MenuInterface::ACTION_DASHBOARD_V),
                array(
                    'menus' => $menuManager->getTranslatedMenuList(),
                )
            ));
        } else {
            return $this->appendNoPartialsHeader($this->render(
                $menuManager->getRegularTemplate(MenuInterface::ACTION_DASHBOARD_H),
                array(
                    'menus' => $menuManager->getTranslatedMenuList(false),
                    'menuAlias' => $menuManager->getCurrentMenu()->getShortClassName(),
                    'no-partials' => 1
                )
            ));
        }
    }

    /**
     * Prepares the Response for particular Menu Index action
     *
     * @param Request $request
     * @param string  $menu
     * @param string  $page
     *
     * @return Response
     */
    public function indexAction(Request $request, $menu, $page) {
        $menuManager = $this->get('evp.ticket_admin.service.menu_manager');
        $menuIndex = $menuManager
            ->setMenuItem($menu)
            ->setRequest($request)
            ->setAction(MenuInterface::ACTION_INDEX)
            ->setTarget($page)
            ->getResponse();

        return $this->appendNoPartialsHeader($menuIndex);
    }

    /**
     * Prepares the Response for particular Menu by specified Action on target Id
     *
     * @param Request $request
     * @param $menu
     * @param $action
     * @param $id
     * @return Response
     */
    public function manageAction(Request $request, $menu, $action, $id) {
        $menuManager = $this->get('evp.ticket_admin.service.menu_manager');
        $menuAction = $menuManager
            ->setMenuItem($menu)
            ->setRequest($request)
            ->setAction($action)
            ->setTarget($id)
            ->getResponse();

        return $this->appendNoPartialsHeader($menuAction);
    }

    /**
     * Adds a 'no partials' header to the response
     * This will force wordpress to not show the header/footer layout
     *
     * @param Response $response
     * @return Response
     */
    public function appendNoPartialsHeader(Response $response)
    {
        $response->headers->add(
            array(
                'no-partials'  => 1,
                'content-type' => 'text/html',
            )
        );
        return $response;
    }
}
