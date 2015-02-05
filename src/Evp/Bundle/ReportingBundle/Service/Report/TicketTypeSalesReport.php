<?php
/**
 * Prepares Report about TicketType Sales
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\ReportingBundle\Service\Report;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\ReportingBundle\Entity\Report;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketTypeSalesReport
 */
class TicketTypeSalesReport implements ReportInterface
{
    const DATE_FORMAT = 'Y-m-d';
    const DATE_COL_NAME = 'column.date';
    const TOTAL_COL_NAME = 'column.total';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'ticket_type_sales';
    }

    /**
     * {@inheritdoc}
     *
     * @param Request $request
     *
     * @return ReportInterface|void
     */
    public function setRequest(Request $request)
    {
        //Request is not needed
    }

    /**
     * {@inheritdoc}
     *
     * @param FormBuilderInterface $builder
     */
    public function injectFormElements(FormBuilderInterface $builder)
    {
        //default set fully satisfies this Report
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getInjectedElementsNames()
    {
        return array();
    }

    /**
     * Gets the data from current Report by given filters
     *
     * @param array $filters
     *
     * @return Report
     */
    public function getReport(array $filters)
    {
        $ticketTypes = $this->entityManager->getRepository('EvpTicketBundle:Event')
            ->findOneBy(array(
                    'id' => $filters['event'],
                ))
            ->getTicketTypes();

        $dateFrom = new \DateTime($filters['dateFrom']);
        $dateTo = new \DateTime($filters['dateTo']);
        $interval = $dateTo->diff($dateFrom);
        $includeTest = array_key_exists('includeTests', $filters);
        $orderDetailsRepo = $this->entityManager->getRepository('EvpTicketBundle:Step\OrderDetails');

        $records = array();
        foreach ($ticketTypes as $ticketType) {
            $data = $orderDetailsRepo->getForTicketTypeSalesReport($ticketType, $dateFrom, $dateTo, $includeTest);
            $records[] = $this->normalizeDateRecords($dateFrom, $interval, $data);
        }

        $report = new Report;
        $report
            ->setCols($this->generateColumns($ticketTypes))
            ->setRows(array_keys(reset($records)))
            ->setData($records)
            ->setTotals($this->calculateTotals($records))
            ->setTotalsRows(array(self::TOTAL_COL_NAME));

        return $report;
    }

    /**
     * Calculates the totals for this report
     *
     * @param array $records
     *
     * @return array
     */
    private function calculateTotals($records)
    {
        $totals = array();
        foreach ($records as $record) {
            $sum = 0;
            foreach ($record as $value) {
                $sum += (int)$value;
            }
            $totals[][self::TOTAL_COL_NAME] = $sum;
        }
        return $totals;
    }

    /**
     * Generates array with additional first column as date
     *
     * @param array $cols
     *
     * @return array
     */
    private function generateColumns($cols)
    {
        $date = new \stdClass;
        $date->name = self::DATE_COL_NAME;
        $columns = array($date);
        foreach ($cols as $col) {
            $columns[] = $col;
        }
        return $columns;
    }

    /**
     * Normalizes records of different length to same length
     *
     * @param \DateTime     $from
     * @param \DateInterval $interval
     * @param array         $records
     *
     * @return array
     */
    private function normalizeDateRecords(\DateTime $from, \DateInterval $interval, $records)
    {
        if ($interval->days == count($records)) {
            return $records;
        }

        for ($i = 0; $i < $interval->days; $i++) {
            $fromDate = clone $from;
            $date = $fromDate->modify("+$i day")->format(self::DATE_FORMAT);
            if (!array_key_exists($date, $records)) {
                $records[$date] = 0;
            }
        }
        uksort($records, function($a, $b) {
                $aa = new \DateTime($a);
                $bb = new \DateTime($b);
                return ($aa->getTimestamp() > $bb->getTimestamp() ? 1 : -1);
            });

        return $records;
    }
}
