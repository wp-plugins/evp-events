<?php
/**
 * Abstract Menu class for most common Menu requirements
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketAdminBundle\Service\TicketAdminAnnotationReader;
use Evp\Bundle\TicketBundle\Service\DiscountManager;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

/**
 * Class MenuAbstract
 */
abstract class MenuAbstract implements MenuInterface {

    const TRANS_PREFIX = 'entity.';
    const TRANS_SUFFIX_P = '.plural';
    const TRANS_SUFFIX_S = '.singular';
    const TRANS_DOMAIN = 'columns';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    protected $translator;

    /**
     * @var array[]
     */
    protected $supplemental;

    /**
     * @var \Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface[]
     */
    protected $commonActions;

    /**
     * @var \Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface[]
     */
    protected $generalActions;

    /**
     * @var \Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface[]
     */
    protected $actions;

    /**
     * @var \Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface
     */
    protected $currentAction;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    protected $router;

    /**
     * @var \Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface[]
     */
    protected $customActions;

    /**
     * @var string
     */
    protected $currentLocale;

    /**
     * @var array
     */
    protected $locales;

    /**
     * @var array
     */
    protected $countryCodes;

    /**
     * @var
     */
    protected $currentCountryCode;

    /**
     * @var
     */
    protected $defaultCountryCode;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Monolog\Logger $log
     * @param \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator
     * @param \Evp\Bundle\TicketBundle\Service\DiscountManager $discountManager
     * @param array $supplemental
     * @param array $locales
     * @param \Symfony\Component\Routing\Router $router
     * @param array $countryCodes
     * @param string $defaultCountryCode
     */
    public function __construct(
        EntityManager $em,
        Logger $log,
        Translator $translator,
        DiscountManager $discountManager,
        $supplemental,
        $locales,
        Router $router,
        array $countryCodes,
        $defaultCountryCode
    ) {
        $this->entityManager = $em;
        $this->logger = $log;
        $this->translator = $translator;
        $this->router = $router;
        $this->supplemental = $supplemental;
        $this->locales = $locales;

        $this->supplemental['discountStrategies'] = $discountManager->getAvailableStrategies();
        $this->supplemental['discountScopes'] = $discountManager->getAvailableScopes();
        $this->supplemental['discountTypes'] = $discountManager->getAvailableTypes();

        $this->countryCodes = $countryCodes;
        $this->defaultCountryCode = $defaultCountryCode;
    }

    /**
     * Sets available actions for particular Menu Service
     *
     * @param \Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface[] $common
     * @param \Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface[] $general
     * @param \Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface[] $custom
     */
    public function setActions($common, $general, $custom = null) {
        $this->commonActions = $common;
        $this->generalActions = $general;
        $this->actions = array_merge($common, $general);
        if (!empty($custom)) {
            $this->customActions = $custom;
            $this->actions = array_merge($this->actions, $custom);
        }
    }

    /**
     * Returns Menu name in singular or plural
     *
     * @param bool $singular
     * @return string
     */
    public function getMenuName($singular = true) {
        $suffix = $singular ? self::TRANS_SUFFIX_S : self::TRANS_SUFFIX_P;
        return $this->translator->trans(
            self::TRANS_PREFIX .$this->menuTransName .$suffix,
            array(),
            self::TRANS_DOMAIN
        );
    }

    /**
     * Gets the Twig template name for given Action
     *
     * @return string
     */
    public function getResponseName() {
        return $this->currentAction->getResponseName();
    }

    /**
     * Sets the Target for currentAction
     *
     * @param string $id
     */
    public function setTarget($id) {
        $this->currentAction->setTarget($id);
    }

    /**
     * Gets the current Target Id
     *
     * @return string
     */
    public function getTarget() {
        return $this->currentAction->getTarget();
    }


    /**
     * Sets the Http Request
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest(Request $request) {
        $this->request = $request;
        $this->currentLocale = $request->getLocale();
    }

    /**
     * Gets the Response tye for currentAction
     *
     * @return string
     */
    public function getActionResponseType() {
       return $this->currentAction->getResponseType();
    }

    /**
     * Sets filters & FQCN for currentAction
     *
     * @param array $filters
     * @return self
     */
    public function setFilters($filters) {
        $this->currentAction
            ->setFilters($filters);
        return $this;
    }

    /**
     * Gets the menuClass name
     *
     * @return string
     */
    public function getShortClassName() {
        return $this->menuClass;
    }

    /**
     * Gets the currentAction
     *
     * @return Action\ActionInterface
     */
    public function getCurrentAction() {
        return $this->currentAction;
    }

    /**
     * Array of Twig parameters for particular Menu Action
     *
     * @return array
     */
    public function getResponseParameters() {
        if ($this->currentAction->getResponseType() === self::RESPONSE_REGULAR) {
            $actions = array_keys($this->commonActions);
            return array(
                'elements' => $this->currentAction->buildResponseParameters(),
                'actions' => array_diff($actions, array($this->currentAction->getName())),
                'menuAlias' => $this->menuClass,
            );
        }
        if ($this->currentAction->getResponseType() === self::RESPONSE_REDIRECT) {
                return $this->currentAction->buildResponseParameters();
        }
    }

    /**
     * @return string
     */
    public function getCurrentCountryCode()
    {
        return $this->currentCountryCode;
    }

    /**
     * @param string $currentCountryCode
     *
     * @return $this
     */
    public function setCurrentCountryCode($currentCountryCode)
    {
        $this->currentCountryCode = $currentCountryCode;
        return $this;
    }

    /**
     * Gets the ReloadUrl based on target class name & Id
     *
     * @param string $class
     * @return string
     */
    protected function getReloadUrl($class) {
        return $this->router->generate(
            'admin_refresh_entity',
            array(
                'entity' => $class,
                'id' => $this->getTarget(),
            )
        );
    }

    /**
     * Reload URL for service scope
     *
     * @param string $serviceScope
     * @param string $targetId
     * @return string
     */
    protected function getReloadUrlForScope($serviceScope, $targetId = null)
    {
        $params = array(
            'serviceKey' => $serviceScope,
        );
        if (!empty($targetId)) {
            $params['serviceRequestId'] = $targetId;
        }
        return $this->router->generate('get_service_response', $params);
    }
}
