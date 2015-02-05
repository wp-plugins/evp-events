<?php

namespace Evp\Bundle\TicketBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Evp\Bundle\PaymentBundle\PaymentHandler\InvoiceHandler;
use Evp\Bundle\TicketBundle\Entity\Order;

/**
 * Class OrderRepository
 * @package Evp\Bundle\TicketBundle\Repository
 */
class OrderRepository extends EntityRepository {
    /**
     * Gets all of the orders that have expired, even though their status says otherwise
     *
     * @return Order[]
     */
    public function getAllWithExpirationDatePastDue() {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        $currentDateTime = new \DateTime('now');
        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');

        return $qb->select('o')
            ->from('Evp\Bundle\TicketBundle\Entity\Order', 'o')
            ->where(
                $qb->expr()->lte('o.stateExpires', ':currentDateTime')
            )
            ->andWhere(
                $qb->expr()->neq('o.status', ':orderStatusExpired')
            )
            ->andWhere(
                $qb->expr()->neq('o.status', ':orderStatusDone')
            )
            ->andWhere(
                $qb->expr()->neq('o.paymentType', ':typeInvoice')
            )
            ->setParameters(
                array(
                    'currentDateTime' => $formattedDateTime,
                    'orderStatusExpired' => Order::STATUS_EXPIRED,
                    'orderStatusDone' => Order::STATUS_DONE,
                    'typeInvoice' => InvoiceHandler::PAYMENT_NAME,
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Get an order by token
     *
     * @param string $token
     * @return Order
     */
    public function getOrderByToken($token)
    {
        return $this->getEntityManager()->getRepository('EvpTicketBundle:Order')->findOneBy(
            array(
                'token' => $token
            )
        );
    }

    /**
     * Gets all Orders with filled data
     * @return Order[]
     */
    public function getAllNotNull() {
        return $this->_em->createQueryBuilder()
            ->select('o')
            ->from('Evp\Bundle\TicketBundle\Entity\Order', 'o')
            ->where('o.dateFinished IS NOT null')
            ->andWhere('o.ticketsCount IS NOT null')
            ->andWhere('o.orderPrice IS NOT null')
            ->andWhere('o.paymentType IS NOT null')
            ->andWhere('o.invoiceRequired IS NOT null')
            ->andWhere('o.token IS NOT null')
            ->andWhere('o.event IS NOT null')
            ->orderBy('o.dateCreated', 'desc')
            ->getQuery()
            ->getResult();
    }
} 
