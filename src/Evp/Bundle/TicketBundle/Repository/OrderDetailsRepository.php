<?php
/**
 * OrderDetails Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Order;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Evp\Bundle\TicketBundle\Entity\User;

/**
 * Class OrderDetailsRepository
 */
class OrderDetailsRepository extends EntityRepository {

    /**
     * Gets all OrderDetails by User & Event
     *
     * @param \Evp\Bundle\TicketBundle\Entity\User $us
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails[]
     */
    public function getAllByUserAndEvent(User $us, Event $ev) {
        return $this->_em->createQueryBuilder()
            ->select('od')
            ->from('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails', 'od')
            ->where('od.user = :us')
            ->andWhere('od.event = :ev')
            ->setParameters(array(
                    'us' => $us,
                    'ev' => $ev,
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Evp\Bundle\TicketBundle\Entity\User $us
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @param string $id
     * @return \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails
     */
    public function getOneByUserAndEventAndId(User $us, Event $ev, $id) {
        return $this->_em->createQueryBuilder()
            ->select('od')
            ->from('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails', 'od')
            ->where('od.user = :us')
            ->andWhere('od.event = :ev')
            ->setParameters(array(
                    'us' => $us,
                    'ev' => $ev,
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets all OrderDetails, where no Discount was applied
     *
     * @param \Evp\Bundle\TicketBundle\Entity\User $us
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @return \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails[]
     */
    public function getNotDiscountedByUserAndEvent(User $us, Event $ev) {
        $orderDetails = $this->_em->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findBy(array(
                    'user' => $us,
                    'event' => $ev,
                )
            );
        $undiscountedIds = array();
        foreach ($orderDetails as $orderDetail) {
            $undiscountedIds[] = $this->_em->createQueryBuilder()
                ->select('DISTINCT IDENTITY (t.orderDetails)')
                ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
                ->join('t.orderDetails', 'od')
                ->where('t.user = :us')
                ->andWhere('t.event = :ev')
                ->andWhere('od.id = :id')
                ->andWhere('t.discount IS null')
                ->setParameters(array(
                        'us' => $us,
                        'ev' => $ev,
                        'id' => $orderDetail->getId(),
                    )
                )
                ->getQuery()
                ->getOneOrNullResult();
        }
        $orderDetails = array();
        foreach (array_filter($undiscountedIds) as $id) {
            $orderDetails[] = $this->_em->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
                ->findOneBy(array(
                        'id' => $id[1],
                    )
                );
        }
        return $orderDetails;
    }

    /**
     * Gets all OrderDetails, where Discount was applied
     *
     * @param \Evp\Bundle\TicketBundle\Entity\User $us
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @return \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails[]
     */
    public function getDiscountedByUserAndEvent(User $us, Event $ev) {
        $orderDetails = $this->_em->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findBy(array(
                    'user' => $us,
                    'event' => $ev,
                )
            );
        $discountedIds = array();
        foreach ($orderDetails as $orderDetail) {
            $discountedIds[] = $this->_em->createQueryBuilder()
                ->select('DISTINCT IDENTITY (t.orderDetails)')
                ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
                ->join('t.orderDetails', 'od')
                ->where('t.user = :us')
                ->andWhere('t.event = :ev')
                ->andWhere('od.id = :id')
                ->andWhere('t.discount IS NOT null')
                ->setParameters(array(
                        'us' => $us,
                        'ev' => $ev,
                        'id' => $orderDetail->getId(),
                    )
                )
                ->getQuery()
                ->getOneOrNullResult();
        }
        $orderDetails = array();
        foreach (array_filter($discountedIds) as $id) {
            $orderDetails[] = $this->_em->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
                ->findOneBy(array(
                        'id' => $id[1],
                    )
                );
        }
        return $orderDetails;
    }

    /**
     * Gets OrderDetails grouped by DateRange for given TicketType where Order is Done
     *
     * @param TicketType $tt
     * @param \DateTime  $from
     * @param \DateTime  $to
     * @param bool       $tests
     *
     * @return array
     */
    public function getForTicketTypeSalesReport(TicketType $tt, \DateTime $from, \DateTime $to, $tests)
    {
        $to->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder('od')
            ->select('DATE(o.dateFinished) AS date, SUM(od.ticketsCount) AS total')
            ->join('od.order', 'o')
            ->where('o.dateFinished BETWEEN :from AND :to')
            ->andWhere('o.status = :st')
            ->andWhere('od.ticketType = :tt');
        if (!$tests) {
            $qb = $qb->andWhere('o.testMode = false');
        }
        $qb = $qb->setParameters(array(
                    'from' => $from,
                    'to' => $to,
                    'tt' => $tt,
                    'st' => Order::STATUS_DONE,
                ))
            ->groupBy('date')
            ->getQuery();

        $result = $qb->getResult();
        $data = array();
        foreach ($result as $row) {
            $data[$row['date']] = $row['total'];
        }
        return $data;
    }

    /**
     * Gets various sums of Tickets by given TicketType
     *
     * @param TicketType $tt
     * @param bool       $tests
     *
     * @return array
     */
    public function getForTicketsLeftReport(TicketType $tt, $tests)
    {
        $statuses = array(
            Order::STATUS_DONE,
            Order::STATUS_IN_PROGRESS,
            Order::STATUS_AWAITING_PAYMENT,
            Order::STATUS_EXPIRED,
        );
        $results = array();

        $results['limit'] = $tt->getTicketsCount();
        foreach ($statuses as $status) {
            $qb = $this->createQueryBuilder('od')
                ->select('SUM(od.ticketsCount)')
                ->join('od.order', 'o')
                ->where('od.ticketType = :tt')
                ->andWhere('o.status = :dn');
            if (!$tests) {
                $qb = $qb->andWhere('o.testMode = false');
            }
            $qb->setParameters(array(
                    'tt' => $tt,
                    'dn' => $status,
                ));
            $results[$status] = $qb->getQuery()->getSingleScalarResult();
        }
        $results['available'] = $tt->getTicketsCount() !== null ?
            $tt->getTicketsCount() - (int)$results[Order::STATUS_DONE] :
            null;
        return $results;
    }
}
