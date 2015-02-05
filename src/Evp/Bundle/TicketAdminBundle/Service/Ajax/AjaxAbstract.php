<?php

namespace Evp\Bundle\TicketAdminBundle\Service\Ajax;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Session;

abstract class AjaxAbstract
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Monolog\logger
     */
    protected $logger;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var int
     */
    protected $scopeId;

    /**
     * @var string
     */
    protected $target;

    /**
     * Holds the evp.ticket_admin.menu_supplemental_items params collection from services.xml
     * @var string []
     */
    protected $params;

    /**
     * Holds the evp.ticket_admin.action_supplements params collection from services.xml
     *
     * @var string []
     */
    protected $actionSupplements;


    /**
     * @param EntityManager $em
     * @param Logger $log
     * @param Session $session
     */
    public function __construct(
        EntityManager $em,
        Logger $log,
        Session $session
    ) {
        $this->entityManager = $em;
        $this->logger = $log;
        $this->session = $session;
    }

    /**
     * Sets the target method of service
     *
     * @param mixed $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Sets the scope id of service for ajax calls
     *
     * @param mixed $scopeId
     */
    public function setScopeId($scopeId)
    {
        $this->scopeId = $scopeId;
    }

    /**
     * Sets scope to the target service, e.g events will call the Events service.
     *
     * @param mixed $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * Sets the evp.ticket_admin.menu_supplemental_items params collection from services.xml file
     *
     * @param $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Sets the evp.ticket_admin.action_supplements params collection from services.xml file
     *
     * @param $actionSupplements
     */
    public function setActionSupplements($actionSupplements)
    {
        $this->actionSupplements = $actionSupplements;
    }
}

