<?php
/**
 * Ticket Repository
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\TicketBundle\Entity\DiscountType;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Order;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Evp\Bundle\TicketBundle\Entity\User;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class TicketRepository
 */
class TicketRepository extends EntityRepository {

    /**
     * Gets Unfilled Tickets by Event & User
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @param \Evp\Bundle\TicketBundle\Entity\User $us
     *
     * @return array
     */
    public function getUnfilledTicketsByEventAndUser(Event $ev, User $us) {
        $hasRequiredFields = $this->_em->createQueryBuilder()
            ->select('etfs')
            ->from('Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema', 'etfs')
            ->where('etfs.eventType = :et')
            ->andWhere('etfs.isRequiredForAll = 1')
            ->andWhere('etfs.isRequired = 1')
            ->setMaxResults(1)
            ->setParameters(
                array(
                    'et' => $ev->getEventType(),
                )
            )
            ->getQuery()
            ->getResult();
        $result = null;
        if (!empty($hasRequiredFields)) {
            $ticketsFilled = $this->_em->createQueryBuilder()
                ->select('fr')
                ->from('Evp\Bundle\TicketBundle\Entity\Form\FieldRecord', 'fr')
                ->where('fr.user = :us')
                ->andWhere('fr.event = :ev')
                ->andWhere('fr.ticket IS NOT null')
                ->setParameters(
                    array(
                        'ev' => $ev,
                        'us' => $us,
                    )
                )
                ->getQuery()
                ->getResult();

            if (empty($ticketsFilled)) {
                $result = $this->_em->createQueryBuilder()
                    ->select('t.id')
                    ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
                    ->where('t.user = :us')
                    ->andWhere('t.event = :ev')
                    ->setParameters(
                        array(
                            'ev' => $ev,
                            'us' => $us,
                        )
                    )
                    ->getQuery()
                    ->getResult();
            } else {
                $ticketIds = array();
                foreach ($ticketsFilled as $record) {
                    $ticketIds[] = $record->getTicket()->getId();
                }
                $result = $this->_em->createQueryBuilder()
                    ->select('t.id')
                    ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
                    ->where('t.user = :us')
                    ->andWhere('t.event = :ev')
                    ->andWhere('t.id NOT IN (:not)')
                    ->setParameters(
                        array(
                            'ev' => $ev,
                            'us' => $us,
                            'not' => array_unique($ticketIds),
                        )
                    )
                    ->getQuery()
                    ->getResult();
            }
        }
        return $result;
    }

    /**
     * Gets Unfilled Tickets by Event & User & OrderDetails
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @param \Evp\Bundle\TicketBundle\Entity\User $us
     * @param \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails $od
     *
     * @return array
     */
    public function getUnfilledTicketsByEventAndUserAndOrderDetails(Event $ev, User $us, OrderDetails $od) {
        $hasRequiredFields = $this->_em->createQueryBuilder()
            ->select('etfs')
            ->from('Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema', 'etfs')
            ->where('etfs.eventType = :et')
            ->andWhere('etfs.isRequiredForAll = 1')
            ->setParameters(
                array(
                    'et' => $ev->getEventType(),
                )
            )
            ->getQuery()
            ->getResult();
        $result = null;
        if (!empty($hasRequiredFields)) {
            $ticketsFilled = $this->_em->createQueryBuilder()
                ->select('DISTINCT IDENTITY (fr.ticket)')
                ->from('Evp\Bundle\TicketBundle\Entity\Form\FieldRecord', 'fr')
                ->where('fr.user = :us')
                ->andWhere('fr.event = :ev')
                ->andWhere('fr.ticket IS NOT null')
                ->setParameters(
                    array(
                        'ev' => $ev,
                        'us' => $us,
                    )
                )
                ->getQuery()
                ->getResult();

            if (empty($ticketsFilled)) {
                $result = $this->_em->createQueryBuilder()
                    ->select('t')
                    ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
                    ->where('t.user = :us')
                    ->andWhere('t.event = :ev')
                    ->andWhere('t.orderDetails = :od')
                    ->setParameters(
                        array(
                            'ev' => $ev,
                            'us' => $us,
                            'od' => $od,
                        )
                    )
                    ->getQuery()
                    ->getResult();
            } else {
                $ticketIds = array();
                foreach ($ticketsFilled as $record) {
                    $ticketIds[] = $record[1];
                }
                $result = $this->_em->createQueryBuilder()
                    ->select('t')
                    ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
                    ->where('t.user = :us')
                    ->andWhere('t.event = :ev')
                    ->andWhere('t.id NOT IN (:not)')
                    ->andWhere('t.orderDetails = :od')
                    ->setParameters(
                        array(
                            'ev' => $ev,
                            'us' => $us,
                            'not' => array_unique($ticketIds),
                            'od' => $od,
                        )
                    )
                    ->getQuery()
                    ->getResult();
            }
        }
        return $result;
    }

    /**
     * Gets all Tickets by Event, User, OrderDetails and without TicketTypeDiscount
     *
     * @param Event $ev
     * @param User $us
     * @param OrderDetails $od
     * @return array
     */
    public function getNotDiscountedByEventAndUserAndOrderDetails(Event $ev, User $us, OrderDetails $od) {
        return $this->createQueryBuilder('t')
            ->where('t.event = :ev')
            ->andWhere('t.user = :us')
            ->andWhere('t.orderDetails = :od')
            ->andWhere('t.discount IS null')
            ->orderBy('t.id', 'asc')
            ->setParameters(
                array(
                    'ev' => $ev,
                    'us' => $us,
                    'od' => $od,
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets filled Tickets in FieldRecord
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $ev
     * @param \Evp\Bundle\TicketBundle\Entity\User $us
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Ticket[]
     */
    public function getFilledTicketsByEventAndUser(Event $ev, User $us) {
        $tickets = array();
        $ids = $this->_em->createQueryBuilder()
            ->select('DISTINCT IDENTITY (fr.ticket)')
            ->from('Evp\Bundle\TicketBundle\Entity\Form\FieldRecord', 'fr')
            ->where('fr.event = :ev')
            ->andWhere('fr.user = :us')
            ->andWhere('fr.ticket IS NOT null')
            ->setParameters(
                array(
                    'ev' => $ev,
                    'us' => $us,
                )
            )
            ->getQuery()
            ->getResult();
        foreach ($ids as $id) {
            $tickets[] = $this->_em->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
                ->findOneBy(array('id' => $id));
        }
        return $tickets;
    }

    /**
     * Gets all Tickets by Event and TicketTypeDiscount[]
     *
     * @param Event $ev
     * @param \Evp\Bundle\TicketBundle\Entity\DiscountType $discountType
     * @return array
     */
    public function getAllByEventAndDiscountType(Event $ev, DiscountType $discountType) {
        return $this->createQueryBuilder('t')
            ->join('t.ticketType', 'tt')
            ->join('tt.discountTypes', 'dt')
            ->andWhere('tt.event = :ev')
            ->andWhere('dt.id = :dtid')
            ->setParameters(array(
                    'ev' => $ev,
                    'dtid' => $discountType->getId(),
                ))
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets one Ticket by parameters
     *
     * @param User $us
     * @param Event $ev
     * @param OrderDetails $od
     * @return mixed
     */
    public function getOneByUserAndEventAndOrderDetail(User $us, Event $ev, OrderDetails $od) {
        return $this->_em->createQueryBuilder()
            ->select('t')
            ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
            ->where('t.user = :us')
            ->andWhere('t.event = :ev')
            ->andWhere('t.orderDetails = :od')
            ->setMaxResults(1)
            ->setParameters(array(
                    'us' => $us,
                    'ev' => $ev,
                    'od' => $od,
                )
            )
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Gets all tickets that belong to the specified order
     *
     * @param Order $order
     * @return \Evp\Bundle\TicketBundle\Entity\Ticket[]
     */
    public function getAllByOrder(Order $order) {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('t')
            ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
            ->leftJoin('t.orderDetails', 'od')
            ->where('od.order = :order')
            ->andWhere($qb->expr()->isNotNull('od.order'))
            ->setParameters(
                array(
                    'order' => $order,
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets all tickets by TicketType & status
     *
     * @param TicketType $tt
     * @param string $st
     * @return \Evp\Bundle\TicketBundle\Entity\Ticket[]
     */
    public function getAllByTicketTypeAndStatus(TicketType $tt, $st) {
        return $this->_em->createQueryBuilder()
            ->select('t')
            ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
            ->where('t.ticketType = :tt')
            ->andWhere('t.status = :st')
            ->setParameters(
                array(
                    'tt' => $tt,
                    'st' => $st,
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets all tickets by given filters or fallback settings
     *
     * @param array $filters
     * @param array $defaults
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Ticket[]
     */
    public function getAllByFilters($filters, $defaults)
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.event', 'e')
            ->andWhere('e.dateStarts >= :starts')
            ->andWhere('e.dateEnds <= :ends');

        if (array_key_exists('event_from', $filters)) {
            $qb->setParameter('starts', new \DateTime('@' .$filters['event_from']));
        } else {
            $qb->setParameter('starts', new \DateTime($defaults['sync_depth_past']));
        }

        if (array_key_exists('event_until', $filters)) {
            $qb->setParameter('ends', new \DateTime('@' .$filters['event_until']));
        } else {
            $qb->setParameter('ends', new \DateTime($defaults['sync_depth_future']));
        }

        if (array_key_exists('from_date', $filters)) {
            $qb->andWhere('t.dateModified > :fromTicket');
            $qb->setParameter('fromTicket', new \DateTime('@' .$filters['from_date']));
        }

        if (array_key_exists('limit', $filters)) {
            $qb->setMaxResults((int)$filters['limit']);
        } else {
            $qb->setMaxResults((int)$defaults['sync_limit']);
        }

        $qb->andWhere('t.status = :ticket_status');
        $qb->setParameter('ticket_status', $filters['status']);
        $qb->orderBy('t.dateModified', 'asc');
        return $qb->getQuery()->getResult();
    }

    /**
     * Sums the total tickets price for current OrderDetail
     *
     * @param OrderDetails $od
     *
     * @return int
     */
    public function sumTicketPriceByOrderDetails(OrderDetails $od)
    {
        return $this->_em->createQueryBuilder()
            ->select('SUM (tt.price)')
            ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
            ->join('t.ticketType', 'tt')
            ->where('t.orderDetails = :od')
            ->setParameters(array(
                    'od' => $od,
                ))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Sums the total tickets price for current User
     *
     * @param \Evp\Bundle\TicketBundle\Entity\User $us
     *
     * @return int
     */
    public function sumTicketPriceByUser(User $us)
    {
        return $this->_em->createQueryBuilder()
            ->select('SUM (t.price)')
            ->from('Evp\Bundle\TicketBundle\Entity\Ticket', 't')
            ->where('t.user = :us')
            ->setParameters(array(
                    'us' => $us,
                ))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
