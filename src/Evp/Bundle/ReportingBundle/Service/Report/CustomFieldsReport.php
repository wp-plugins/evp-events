<?php
/**
 * Prepares Report about Custom fields filled for Tickets
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\ReportingBundle\Service\Report;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\ReportingBundle\Entity\Report;
use Evp\Bundle\ReportingBundle\Form\GenericReportForm;
use Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CustomFieldsReport
 */
class CustomFieldsReport implements ReportInterface
{
    const DATE_FORMAT = 'Y-m-d';
    const TICKET_COL_NAME = 'column.ticket';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Request
     */
    private $request;

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
        return 'custom_fields';
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
        $this->request = $request;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param FormBuilderInterface $builder
     */
    public function injectFormElements(FormBuilderInterface $builder)
    {
        $fields = $this->getFields($this->request);
        foreach ($fields as $key => $field) {
            if (!$field->getIsMadeByAdmin()) {
                unset($fields[$key]);
            }
        }
        $builder->add('fields', 'entity', array(
                'class' => 'EvpTicketBundle:Form\EventTypeFieldSchema',
                'property' => 'fieldSchema',
                'multiple' => true,
                'expanded' => false,
                'choices' => $fields,
                'attr' => array(
                    'class' => 'addedGroup',
                ),
            ));
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getInjectedElementsNames()
    {
        return array('fields');
    }

    /**
     * @param Request $request
     *
     * @return EventTypeFieldSchema[]
     */
    private function getFields(Request $request)
    {
        $data = $request->request->get(GenericReportForm::FORM_NAME);
        $event = $this->entityManager->getRepository('EvpTicketBundle:Event')
            ->findOneBy(array(
                    'id' => $data['event']
                ));
        return $event->getEventType()->getEventFieldSchemas();
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
        $event = $this->entityManager->getRepository('EvpTicketBundle:Event')
            ->findOneBy(array(
                    'id' => $filters['event'],
                ));

        $fieldSchemas = array();
        foreach ($filters['fields'] as $id) {
            $fieldSchemas[] = $this->entityManager->getRepository('EvpTicketBundle:Form\EventTypeFieldSchema')
                ->findOneBy(array(
                        'id' => $id,
                        'isMadeByAdmin' => true,
                    ))->getFieldSchema();
        }

        $dateFrom = new \DateTime($filters['dateFrom']);
        $dateTo = new \DateTime($filters['dateTo']);
        $includeTest = array_key_exists('includeTests', $filters);

        $recordsRepo = $this->entityManager->getRepository('EvpTicketBundle:Form\FieldRecord');
        $records = array();
        foreach ($fieldSchemas as $schema) {
            $records[] = $recordsRepo
                ->getForCustomFieldsReport($event, $schema, $dateFrom, $dateTo, $includeTest);
        }

        $normalizedRecords = $this->normalizeRecords($records);
        $report = new Report;
        $report
            ->setCols($this->generateColumns($fieldSchemas))
            ->setData($normalizedRecords)
            ->setRows(array_keys(reset($normalizedRecords)));

        return $report;
    }

    /**
     * @param array $records
     *
     * @return array
     */
    private function normalizeRecords($records)
    {
        $max = 0;
        $keys = array();
        foreach ($records as $record) {
            if (count($record) > $max) {
                $max = count($record);
                $keys = array_keys($record);
            }
        }
        $normal = array();
        foreach ($records as $record) {
            if (count($record) < $max) {
                foreach ($keys as $key) {
                    if (!array_key_exists($key, $record)) {
                        $record[$key] = '-';
                    }
                }
            }
            ksort($record);
            $normal[] = $record;
        }
        return $normal;
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
        $date->name = self::TICKET_COL_NAME;
        $columns = array($date);
        foreach ($cols as $col) {
            $columns[] = $col;
        }
        return $columns;
    }
}
