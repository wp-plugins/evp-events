<?php
/**
 * Prepares Report about Unsold/total Tickets for each Ticket type
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\ReportingBundle\Service\Report;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\ReportingBundle\Entity\Report;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketsLeftReport
 */
class TicketsLeftReport implements ReportInterface
{
    const STATUS_COL_NAME = 'column.property';

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
     * Gets the name of current Report
     *
     * @return string
     */
    public function getName()
    {
        return 'tickets_left';
    }

    /**
     * Injects additional Form elements to Builder
     *
     * @param FormBuilderInterface $builder
     */
    public function injectFormElements(FormBuilderInterface $builder)
    {
        $builder->get('dateFrom')->setAttribute('required', false);
        $builder->get('dateTo')->setAttribute('required', false);
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
        $includeTest = array_key_exists('includeTests', $filters);

        $orderDetailsRepo = $this->entityManager->getRepository('EvpTicketBundle:Step\OrderDetails');
        $data = array();
        foreach ($ticketTypes as $type) {
            $data[] = $orderDetailsRepo->getForTicketsLeftReport($type, $includeTest);
        }

        $report = new Report;
        $report
            ->setCols($this->generateColumns($ticketTypes))
            ->setRows(array_keys(reset($data)))
            ->setData($data);

        return $report;
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
        $date->name = self::STATUS_COL_NAME;
        $columns = array($date);
        foreach ($cols as $col) {
            $columns[] = $col;
        }
        return $columns;
    }

    /**
     * Store the Request
     *
     * @param Request $request
     *
     * @return ReportInterface
     */
    public function setRequest(Request $request)
    {
        return $this;
    }

    /**
     * Gets the Names of injected Form Elements
     *
     * @return array
     */
    public function getInjectedElementsNames()
    {
        return array();
    }

} 
