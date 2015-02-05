<?php
/**
 * Listens on Order Events
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */
namespace Evp\Bundle\TicketBundle\Listener\Order;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\EventDispatcher\Event\Order\Expired;
use Evp\Bundle\TicketBundle\Service\TicketTokenManager;

/**
 * Class TicketTokenListener
 */
class TicketTokenListener
{
    /**
     * @var TicketTokenManager
     */
    private $ticketTokenManager;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param TicketTokenManager $manager
     * @param EntityManager      $em
     */
    public function __construct(
        TicketTokenManager $manager,
        EntityManager $em
    ) {
        $this->ticketTokenManager = $manager;
        $this->entityManager = $em;
    }

    /**
     * Frees-up expired tokens
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
                $this->ticketTokenManager->unbindTokenByTicket($ticket);
            }
        }
    }
} 
