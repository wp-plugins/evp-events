<?php
/**
 * General Menu Action interface for common methods
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action;

/**
 * Interface ActionInterface
 */
interface ActionInterface {

    const ACTION_INDEX = 'index';
    const ACTION_EDIT = 'edit';
    const ACTION_DELETE = 'delete';
    const ACTION_ADD = 'add';

    const RESPONSE_REGULAR = 'response';
    const RESPONSE_REDIRECT = 'redirect';
    const ROUTE_INDEX = 'admin_manage_menu_index';

    /**
     * Sets filters for Action
     *
     * @param array $filters
     * @return self
     */
    function setFilters($filters);

    /**
     * Sets all parameters for Action
     *
     * @param array $params
     * @return self
     */
    function setParameters($params);

    /**
     * Sets the target Id for current Action
     *
     * @param string $id
     */
    function setTarget($id);

    /**
     * Gets the target Id for current Action
     *
     * @return string
     */
    function getTarget();

    /**
     * Gets the Action result
     *
     * @return mixed
     */
    function getResult();

    /**
     * Gets the name of the Action
     *
     * @return string
     */
    function getName();

    /**
     * Builds necessary template params by given FQCN
     *
     * @return array
     */
    function buildResponseParameters();

    /**
     * Gets the Twig template name or Redirect route name
     *
     * @return string
     */
    function getResponseName();

    /**
     * Gets the Response type for currentAction
     *
     * @return string
     */
    function getResponseType();
}
