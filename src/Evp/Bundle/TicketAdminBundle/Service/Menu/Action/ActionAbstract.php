<?php
/**
 * ActionAbstract class for most common Menu Action requirements
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketAdminBundle\Service\TicketAdminAnnotationReader;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

/**
 * Class ActionAbstract
 */
abstract class ActionAbstract {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Evp\Bundle\TicketAdminBundle\Service\TicketAdminAnnotationReader
     */
    protected $annotationReader;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var string[]
     */
    protected $templates;

    /**
     * @var string
     */
    protected $targetId;

    /**
     * @var string
     */
    protected $fqcn;

    /**
     * @var string
     */
    protected $shortClassName;

    /**
     * @var object
     */
    protected $form;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    protected $translator;

    /**
     * @var string[]
     */
    protected $redirects;

    /**
     * @var string[]
     */
    protected $redirectParents;

    /**
     * @var array[]
     */
    protected $parent = null;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;

    /**
     * @var array
     */
    protected $supplements;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    protected $router;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Monolog\Logger $log
     * @param \Evp\Bundle\TicketAdminBundle\Service\TicketAdminAnnotationReader $reader
     * @param \Symfony\Component\Form\FormFactory $formFactory
     * @param string $supplements
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @param \Symfony\Component\Routing\Router $router
     * @param \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator
     */
    public function __construct(
        EntityManager $em,
        Logger $log,
        TicketAdminAnnotationReader $reader,
        FormFactory $formFactory,
        $supplements,
        Session $session,
        Router $router,
        Translator $translator
    ) {
        $this->entityManager = $em;
        $this->logger = $log;
        $this->annotationReader = $reader;
        $this->formFactory = $formFactory;
        $this->templates = $supplements['regular_templates'];
        $this->redirects = $supplements['menu_redirects'];
        $this->redirectParents = $supplements['redirect_parents'];
        $this->session = $session;
        $this->supplements = $supplements;
        $this->router = $router;
        $this->translator = $translator;

        if ($session->has($supplements['parent_session_key'])) {
            $this->parent = $session->get($supplements['parent_session_key']);
        }
    }

    /**
     * Gets the current Action name
     *
     * @return string
     */
    public function getName() {
        return $this->actionName;
    }

    /**
     * Removes the Parent from Session
     */
    protected function removeParent() {
        $this->session->remove($this->supplements['parent_session_key']);
    }

    /**
     * Sets the Parent to Session
     *
     * @param array $parent
     */
    public function setParent($parent) {
        $this->parent = $parent;
        $this->session->set($this->supplements['parent_session_key'], $parent);
    }

    /**
     * Gets the Parent for current Menu action
     * @return mixed
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Sets filters for Action
     *
     * @param array $filters
     * @return self
     */
    public function setFilters($filters) {
    }

    /**
     * Gets the Result gy Entity & Filters
     *
     * @return array
     */
    public function getResult() {
    }

    /**
     * Sets the Request
     *
     * @param Request $request
     * @return self
     */
    public function setRequest(Request $request) {
        $this->request = $request;
        return $this;
    }

    /**
     * Sets the currentMenu short name
     *
     * @param string $name
     * @return self
     */
    public function setShortClassName($name) {
        $this->shortClassName = $name;
        return $this;
    }

    /**
     * Gets the current Action Template for Twig
     *
     * @return string
     */
    public function getResponseName() {
        if (array_key_exists($this->actionName, $this->templates)) {
            return $this->templates[$this->actionName];
        } else {
            return ActionInterface::ROUTE_INDEX;
        }
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
     * Sets the current object's Id
     *
     * @param string $id
     */
    public function setTarget($id) {
        $this->targetId = $id;
    }

    /**
     * Gets the target object's Id
     *
     * @return string
     */
    public function getTarget() {
        return $this->targetId;
    }

    /**
     * Sets the current FQCN
     *
     * @param string $fqcn
     */
    public function setFqcn($fqcn) {
        $this->fqcn = $fqcn;
    }

    /**
     * Returns the Response type
     *
     * @return string
     */
    public function getResponseType() {
        return $this->responseType;
    }

    /**
     * Gets parent Entity if found
     *
     * @return object
     */
    protected function getParentEntity() {
        $parent = null;
        if (!empty($this->parent)) {
            $parent = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\\' .ucfirst($this->parent['class']))
                ->findOneBy(
                    array(
                        'id' => $this->parent['id'],
                    )
                );
        }
        return $parent;
    }
}
