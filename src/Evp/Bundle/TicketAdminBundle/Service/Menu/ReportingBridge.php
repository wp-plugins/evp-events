<?php
/**
 * Bridges Reports Menu to ReportingBundle
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

use Evp\Bundle\ReportingBundle\Service\ReportManager;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ReportingBridge
 */
class ReportingBridge extends MenuAbstract implements MenuInterface
{

    /**
     * @var string
     */
    protected $menuClass = 'Report';

    /**
     * @var string
     */
    protected $menuTransName = 'report';

    /**
     * @var ReportManager
     */
    private $reportManager;

    /**
     * @param ReportManager $reportManager
     */
    public function setReportManager(ReportManager $reportManager)
    {
        $this->reportManager = $reportManager;
    }

    /**
     * Sets current Action name
     *
     * @param string $action
     */
    public function setCurrentAction($action)
    {
    }

    /**
     * Sets the Target for currentAction
     *
     * @param string $id
     */
    public function setTarget($id)
    {
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->reportManager->setRequest($request);
    }

    /**
     * Gets the current Action Template for Twig
     *
     * @return string
     */
    public function getResponseName()
    {
        return $this->reportManager->getTemplate();
    }

    /**
     * Gets the Response tye for currentAction
     *
     * @return string
     */
    public function getActionResponseType()
    {
        return ActionInterface::RESPONSE_REGULAR;
    }

    /**
     * Array of Twig parameters for particular Menu Action
     *
     * @return array
     */
    public function getResponseParameters()
    {
        return array(
            'form' => $this->reportManager->getFormView(),
            'report' => $this->reportManager->getReport(),
        );
    }

    /**
     * Gets the sub-menus by [action] = [translation.tag] pattern
     *
     * @return array
     */
    public function getSpecificMenuItems()
    {
        return array(
            'submenus' => array(),
            'texts' => array(),
        );
    }
}
