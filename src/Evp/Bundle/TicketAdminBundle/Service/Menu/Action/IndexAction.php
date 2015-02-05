<?php
/**
 * General Index action to display & filter Entities
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action;

use Doctrine\ORM\Query\QueryException;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\Provider\FilterProvider;

/**
 * Class IndexAction
 */
class IndexAction extends ActionAbstract implements ActionInterface {

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
     * @var FilterProvider
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
     * Gets the Result gy Entity & Filters
     *
     * @return array
     */
    public function getResult() {
        $this->checkParent();
        $this->filterProvider->setRequest($this->request);
        $this->filterProvider->setShortClassName($this->shortClassName);

        return $this->filterProvider->getFilteredResult((int)$this->targetId);
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
            'paging' => array(
                'total' => $this->filterProvider->getPagesCount(),
                'current' => (int)$this->targetId,
            ),
        );
    }
}
