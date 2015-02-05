<?php
/**
 * Listens on Order Events
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Listener\Order;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Seat\Matrix;
use Evp\Bundle\TicketBundle\EventDispatcher\Event\Order\Expired;

/**
 * Class SeatListener
 */
class SeatListener
{
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
     * Frees-up expired seats
     *
     * @param Expired $order
     */
    public function onOrderExpired(Expired $order)
    {
        $orderDetails = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findBy(
                array(
                    'order' => $order->getOrder(),
                )
            );
        foreach ($orderDetails as $detail) {
            $tickets = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
                ->findBy(
                    array(
                        'orderDetails' => $detail,
                    )
                );
            foreach ($tickets as $ticket) {
                $ticket->setSeat(null);
            }
            $seats = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Seat\Matrix')
                ->findBy(
                    array(
                        'orderDetails' => $detail,
                    )
                );
            foreach ($seats as $seat) {
                $seat->setOrderDetails(null);
                $seat->setStatus(Matrix::STATUS_FREE);
            }
        }
    }
} 
