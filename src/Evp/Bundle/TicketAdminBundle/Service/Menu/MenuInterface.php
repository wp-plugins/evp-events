<?php
/**
 * MenuInterface for required methods
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

use Symfony\Component\HttpFoundation\Request;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;

/**
 * Interface MenuInterface
 */
interface MenuInterface {

    const ACTION_DASHBOARD_H = 'horizontal';
    const ACTION_DASHBOARD_V = 'vertical';
    const ACTION_INDEX = ActionInterface::ACTION_INDEX;
    const ACTION_EDIT = ActionInterface::ACTION_EDIT;
    const ACTION_DELETE = ActionInterface::ACTION_DELETE;
    const ACTION_ADD = ActionInterface::ACTION_ADD;


    const RESPONSE_REGULAR = ActionInterface::RESPONSE_REGULAR;
    const RESPONSE_REDIRECT = ActionInterface::RESPONSE_REDIRECT;

    /**
     * Returns Menu name in singular or plural
     *
     * @param bool $singular
     * @return string
     */
    function getMenuName($singular = true);

    /**
     * Sets current action for Menu
     *
     * @param string $action
     */
    function setCurrentAction($action);

    /**
     * Sets available actions for particular Menu Service
     *
     * @param \Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface[] $common
     * @param \Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface[] $general
     */
    function setActions($common, $general);

    /**
     * Sets result filtering options
     *
     * @param $filters
     * @return self
     */
    function setFilters($filters);

    /**
     * Sets the target object Id for current Action
     *
     * @param string $id
     */
    function setTarget($id);

    /**
     * Sets the Request to currentAction
     *
     * @param Request $request
     */
    function setRequest(Request $request);

    /**
     * Gets the Twig template name or Redirect name
     *
     * @return string
     */
    function getResponseName();

    /**
     * Array of Twig parameters for particular Menu
     *
     * @return array
     */
    function getResponseParameters();

    /**
     * Gets the Response type for currentAction
     *
     * @return string
     */
    function getActionResponseType();

    /**
     * Gets the specific list of sub-menu items by [action] = [translation.tag] pattern
     *
     * @return array
     */
    function getSpecificMenuItems();
}
