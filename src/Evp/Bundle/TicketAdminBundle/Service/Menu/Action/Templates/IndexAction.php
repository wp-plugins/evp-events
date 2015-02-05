<?php
/**
 * Custom Index action to display Templates
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\Templates;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;

/**
 * Class IndexAction
 */
class IndexAction extends ActionAbstract implements ActionInterface {

    const PARENT_NAMESPACE = 'Evp\Bundle\TicketBundle\Entity\\';

    /**
     * @var string
     */
    protected $actionName = 'index';

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_REGULAR;

    /**
     * @var int
     */
    private $limit = 50;

    /**
     * Gets the Result gy Entity & Filters
     *
     * @return array
     */
    public function getResult() {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('a')
            ->from($this->fqcn, 'a')
            ->where('a.parentClass = :pk')
            ->andWhere('a.foreignKey = :fk')
            ->setParameters(
                array(
                    'pk' => get_class($this->parent),
                    'fk' => $this->parent->getId(),
                )
            )
            ->orderBy('a.id', 'desc')
            ->setMaxResults($this->limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Sets Parameters for Action
     *
     * @param array $params
     * @return self
     */
    public function setParameters($params) {
        $this->fqcn = $params['fqcn'];
        $this->parent = $params['parent'];
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
            'filters' => array(),
            'records' => $this->getResult(),
        );
    }
}
