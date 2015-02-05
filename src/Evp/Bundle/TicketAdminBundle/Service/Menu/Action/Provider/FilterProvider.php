<?php
/**
 * Provides filtering capabilities for Menu, if enabled
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\Provider;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Evp\Bundle\TicketAdminBundle\Form\GenericFilterForm;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FilterProvider
 */
class FilterProvider extends ActionAbstract {

    const MAX_RESULTS = 20;

    /**
     * @var array
     */
    private $fieldsToSkip = array(
        '_token',
    );

    /**
     * @var array
     */
    private $filterData;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var int
     */
    private $resultCount;

    /**
     * Gets the View for Filter
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function getProvidedView(Request $request) {
        $filterableClass = new $this->fqcn;
        $filterableColumns = $this->annotationReader->getFilterableColumns($filterableClass);
        foreach ($filterableColumns as $key => $col) {
            if (is_string($key)) {
                $filterableColumns[$col] = $key;
                unset($filterableColumns[$key]);
            }
        }

        if (empty($filterableColumns)) {
            return array();
        }
        $form = new GenericFilterForm($filterableColumns);
        $form->setParameters(
            array(
                'translator' => $this->translator,
            )
        );
        $formView = $this->formFactory->create($form)->handleRequest($request)->createView();

        return $formView;
    }

    /**
     * Gets how many pages are for current Filtering Request
     *
     * @return int
     */
    public function getPagesCount()
    {
        return ceil($this->resultCount/self::MAX_RESULTS);
    }

    /**
     * Gets the filtered on non-filtered result array
     *
     * @param int $page
     *
     * @return array
     */
    public function getFilteredResult($page)
    {
        $firstResultNumber = $page * self::MAX_RESULTS - self::MAX_RESULTS;
        if (count($this->request->request)) {
            $this->filterData = $this->prepareFilters();
        }
        $this->columns = $this->getFilterableColumns();
        if (empty($this->columns) || empty($this->filterData)) {
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('a');
            $qb->from($this->fqcn, 'a');
            if (!empty($this->parent)) {
                $qb->join('a.' .$this->parent['class'], 'parent');
                $qb->where('parent.id = :id');
                $qb->setParameter('id', $this->parent['id']);
            }
            $qb->orderBy('a.id', 'desc');
            $qb->setFirstResult($firstResultNumber);
            $qb->setMaxResults(self::MAX_RESULTS);

            $paginator = new Paginator($qb->getQuery());
            $this->resultCount = $paginator->count();
            return $paginator;
        }
        return $this->buildFilteredResult($page);
    }

    /**
     * Builds the filtered Result
     *
     * @param int $page
     *
     * @return array
     */
    public function buildFilteredResult($page)
    {
        $firstResultNumber = $page * self::MAX_RESULTS - self::MAX_RESULTS;
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a');
        $qb->from($this->fqcn, 'a');

        foreach ($this->filterData as $filter => $value) {
            $prefix = '';
            if (array_key_exists($filter, $this->columns)) {
                $target = $this->columns[$filter];

                $qb->join('a.'.$filter, 'b__'.$filter);
                $qb->andWhere('b__'.$filter .'.' .$target .' LIKE :'.$filter);
                $qb->setParameter($filter, '%'.$value.'%');
                continue;
            }
            $qb->andWhere('a.' .$prefix .$filter .' LIKE :'.$filter);
            $qb->setParameter($filter, '%'.$value.'%');
        }

        $qb->orderBy('a.id', 'desc');
        $qb->setFirstResult($firstResultNumber);
        $qb->setMaxResults(self::MAX_RESULTS);

        $paginator = new Paginator($qb->getQuery());
        $this->resultCount = $paginator->count();

        return $paginator;
    }

    /**
     * Gets the valid filters from Request
     *
     * @return array
     */
    private function prepareFilters() {
        $form = new GenericFilterForm;
        $filters = $this->request->get($form->getName());
        foreach ($this->fieldsToSkip as $skip) {
            unset($filters[$skip]);
        }
        return array_filter($filters);
    }

    /**
     * Returns filtered columns if current Entity can be filtered
     *
     * @return bool|array
     */
    private function getFilterableColumns() {
        $filterableColumns = $this->annotationReader->getFilterableColumns(new $this->fqcn);
        if (!empty($filterableColumns)) {
            return $filterableColumns;
        }
        return false;
    }
}
