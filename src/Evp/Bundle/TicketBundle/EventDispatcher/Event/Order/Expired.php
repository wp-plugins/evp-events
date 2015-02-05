<?php
/**
 * Expired Order Event
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\EventDispatcher\Event\Order;

use Evp\Bundle\TicketBundle\Entity\Order;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class Expired
 */
class Expired extends Event
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return \Evp\Bundle\TicketBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }
} 
