<?php
/**
 * Specific Index action to display QR code in Index
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\EventMenu;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;

/**
 * Class ExaminersIndexAction
 */
class ExaminersIndexAction extends ActionAbstract implements ActionInterface {

    const TEMPLATE_NAME = 'EvpTicketAdminBundle:Event:QrIndex.html.twig';

    /**
     * @var string
     */
    protected $actionName = 'index';

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_REGULAR;

    /**
     * @var array
     */
    private $filterBy = array();

    /**
     * @var array
     */
    private $orderBy = array('id' => 'desc');

    /**
     * @var int
     */
    private $limit = 20;

    /**
     * @var int
     */
    private $offset = null;

    /**
     * @var
     */
    private $providers;

    /**
     * @var
     */
    private $filterProvider;

    /**
     * Sets the currently available providers
     *
     * @param $providers
     */
    public function setProviders($providers) {
        $this->providers = $providers;
        $this->filterProvider = $providers['filterProvider'];
    }

    /**
     * Gets twig template
     *
     * @return string
     */
    public function getResponseName() {
        return self::TEMPLATE_NAME;
    }

    /**
     * Gets the Result gy Entity & Filters
     *
     * @return array
     */
    public function getResult() {
        $this->checkParent();
        $this->filterProvider->setRequest($this->request);
        $this->filterProvider->setShortClassName($this->shortClassName);

         return $this->filterProvider->getFilteredResult($this->targetId);
    }

    public function checkParent() {
        if (!array_key_exists($this->shortClassName, $this->redirectParents)) {
            $this->removeParent();
            $this->parent = null;
            $this->filterProvider->setParent($this->parent);
        } else {
            $parent = $this->getParent();
            if (!empty($parent)) {
                $this->filterBy[$parent['class']] = $parent['id'];
            }
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
        $this->form = $params['form'];
        $this->filterProvider->setFqcn($this->fqcn);
        $this->filterProvider->setParent($this->parent);
        return $this;
    }

    /**
     * Builds template parameters by FQCN
     *
     * @return array
     */
    public function buildResponseParameters() {
        return array(
            'columns' => $this->annotationReader->getListedColumns(new $this->fqcn),
            'filters' => $this->filterProvider->getProvidedView($this->request),
            'records' => $this->getResult(),
        );
    }
}
