<?php

namespace Evp\Bundle\TicketAdminBundle\Service\Ajax;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;

interface AjaxInterface
{
    /**
     * Returns array for ajax response
     *
     * @return array
     */
    function getResult();

    /**
     * Sets the method of target service
     *
     * @param string $string
     * @return self
     */
    function setTarget($string);

    /**
     * Sets the scope to the target service, e.g events will call the Events service.
     *
     * @param string $string
     * @return self
     */
    function setScope($string);

    /**
     * Sets the ID of the target service
     *
     * @param int $id
     * @return self
     */
    function setScopeId($id);

    /**
     * Sets the evp.ticket_admin.menu_supplemental_items params collection from services.xml file
     *
     * @param array $params
     * @return mixed
     */
    function setParams($params);


    /**
     * Sets the evp.ticket_admin.action_supplements params collection from services.xml file
     *
     * @param array $actionSupplements
     * @return mixed
     */
    function setActionSupplements($actionSupplements);
}
