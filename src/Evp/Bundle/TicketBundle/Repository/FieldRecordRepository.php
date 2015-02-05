<?php
/**
 * FieldRecordRepository Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */


namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Form\FieldRecord;
use Evp\Bundle\TicketBundle\Entity\Form\FieldSchema;
use Evp\Bundle\TicketBundle\Entity\Order;
use Evp\Bundle\TicketBundle\Entity\User;

/**
 * Class FieldRecordRepository
 */
class FieldRecordRepository extends EntityRepository {

    /**
     * Gets all records by FieldSchemaId and Event
     *
     * @param array $ids
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @return \Evp\Bundle\TicketBundle\Entity\Form\FieldRecord[]
     */
    public function getAllBySchemaIdAndEvent($ids, Event $ev) {
        return $this->_em->createQueryBuilder()
            ->select('fr')
            ->from('Evp\Bundle\TicketBundle\Entity\Form\FieldRecord', 'fr')
            ->join('fr.fieldSchema', 'fs')
            ->where('fs.id IN (:id)')
            ->andWhere('fr.event = :ev')
            ->setParameters(
                array(
                    'id' => $ids,
                    'ev' => $ev,
                )
            )
            ->orderBy('fr.id', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets Filled Records gy given Event and Schemas collection for finished Orders.
     * Also includes test Orders or not.
     *
     * @param Event                                            $ev
     * @param \Evp\Bundle\TicketBundle\Entity\Form\FieldSchema $schema
     * @param \DateTime                                        $from
     * @param \DateTime                                        $to
     * @param bool                                             $tests
     *
     * @return array[]
     */
    public function getForCustomFieldsReport(
        Event $ev,
        FieldSchema $schema,
        \DateTime $from,
        \DateTime $to,
        $tests
    ) {
        $to->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder('fr')
            ->select('t.id AS ticket, fr.value AS value')
            ->join('fr.ticket', 't')
            ->join('t.orderDetails', 'od')
            ->join('od.order', 'o')
            ->where('fr.event = :ev')
            ->andWhere('fr.fieldSchema = :fs')
            ->andWhere('o.dateFinished BETWEEN :from AND :to')
            ->andWhere('o.status = :st');
        if (!$tests) {
            $qb = $qb->andWhere('o.testMode = false');
        }
        $data = $qb->setParameters(array(
                'ev' => $ev,
                'fs' => $schema,
                'from' => $from,
                'to' => $to,
                'st' => Order::STATUS_DONE,
            ))
            ->getQuery()
            ->getResult();

        $report = array();
        foreach ($data as $row) {
            $report[$row['ticket']] = $row['value'];
        }
        return $report;
    }

    /**
     * @param \Evp\Bundle\TicketBundle\Entity\User $user
     *
     * @return array
     */
    public function getRecordsForOrderByUser(User $user)
    {
        return $this->createQueryBuilder('r')
            ->select('r.value, fs.name, fs.label')
            ->join('r.fieldSchema', 'fs')
            ->where('r.user = :user')
            ->andWhere('r.ticket IS NULL')
            ->setParameters(array(
                    'user' => $user
                ))
            ->getQuery()
            ->getArrayResult();
    }
}
