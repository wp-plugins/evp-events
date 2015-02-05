<?php
/**
 * Order & OrderDetails manager
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketBundle\Entity\Order;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Evp\Bundle\TicketBundle\Entity\User;
use Monolog\Logger;
use Evp\Bundle\TicketBundle\Entity\Seat\Matrix;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

/**
 * Class OrderManager
 */
class OrderManager extends ManagerAbstract {
    const RESERVATION_SHORT = 'short';
    const RESERVATION_LONG = 'long';

    const STATUS_ORDER_DETAILS_NOT_FOUND = 'error.ticket_type_discount_step.order_details_not_found';
    const STATUS_ORDER_DETAILS_OK = 'OK';

    const ORDER_DETAILS_VALIDATION_MAX_LIMIT_PER_USER = 'max.ticket.limit_per_user';
    const ORDER_DETAILS_VALIDATION_NOT_AVAILABLE_COUNT = 'available.to.buy.count';
    const ORDER_DETAILS_VALIDATION_NON_ZERO = 'value.greater.than';

    const INVOICE_FINAL_TEMPLATE = 'invoice_final.html.twig';
    const INVOICE_PROFORMA_TEMPLATE = 'invoice_proforma.html.twig';

    /**
     * @var array
     */
    private $reservationTimes;

    /**
     * @var UserSession
     */
    private $userSession;

    /**
     * @var string
     */
    private $eventSessionKey;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var bool
     */
    private $payseraTest;

    /**
     * @param EntityManager                                     $entityManager
     * @param \Monolog\Logger                                   $logger
     * @param array                                             $reservationTimes
     * @param UserSession                                       $userSession
     * @param                                                   $eventSessionKey
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @param string                                            $payseraTest
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        array $reservationTimes,
        UserSession $userSession,
        $eventSessionKey,
        Session $session,
        $payseraTest
    ) {
        parent::__construct($entityManager, $logger);
        $this->reservationTimes = $reservationTimes;
        $this->userSession = $userSession;
        $this->eventSessionKey = $eventSessionKey;
        $this->session = $session;
        $this->payseraTest = $payseraTest == '1' ? true : false;
    }

    /**
     * Creates new Order for current User
     *
     * @param User $user
     *
     * @return Order
     */
    public function createFromUser(User $user) {
        $nearFuture = $this->reservationTimes[self::RESERVATION_SHORT];

        $order = Order::create()
            ->setUser($user)
            ->setStatus(Order::STATUS_IN_PROGRESS)
            ->setStateExpires(new \DateTime($nearFuture))
        ;

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    /**
     * Removes OrderDetails & Tickets by provided User, Event, OrderDetails
     *
     * @param \Evp\Bundle\TicketBundle\Entity\User $user
     * @param \Evp\Bundle\TicketBundle\Entity\Event $event
     * @param \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails $orderDetails
     */
    public function removeOrderDetailsAndTickets(User $user, Event $event, OrderDetails $orderDetails) {
        $tickets = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->findBy(array(
                    'user' => $user,
                    'event' => $event,
                    'orderDetails' => $orderDetails,
                )
            );
        foreach ($tickets as $ticket) {
            $this->entityManager->remove($ticket);
        }
        $this->entityManager->remove($orderDetails);
        $this->entityManager->flush();
    }

    /**
     * Extends the order reservation time for a little bit more
     *
     * @param Order $order
     */
    public function extendShortTermReservationTime(Order $order) {
        if (!$this->hasOrderExpired($order)) {
            $order->setStateExpires(new \DateTime($this->reservationTimes[self::RESERVATION_SHORT]));
        }
    }

    /**
     * Updates and persists TicketCount & Price fields in Order Entity
     *
     * @param Order $order
     */
    public function updateTicketCountAndPrice(Order $order) {
        /** @var \Evp\Bundle\TicketBundle\Entity\Ticket[] $ticketsBought */
        $ticketsBought = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->getAllByOrder($order);

        $totalPrice = 0;

        foreach ($ticketsBought as $ticket) {
            $totalPrice += $ticket->getPrice();
        }

        $order->setOrderPrice($totalPrice);
        $order->setTicketsCount(count($ticketsBought));
        $this->entityManager->flush($order);
    }

    /**
     * Extends the order reservation time (longer than short-term reservation)
     * This is done once the user is redirected to the payment system
     *
     * @param Order $order
     */
    public function extendLongTermReservationTime(Order $order) {
        if (!$this->hasOrderExpired($order)) {
            $order->setStateExpires(new \DateTime($this->reservationTimes[self::RESERVATION_LONG]));
        }
    }

    /**
     * Checks the order expiration date
     *
     * @param Order $order
     * @return bool
     */
    public function hasOrderExpired(Order $order)
    {
        $expirationDatePastDue = $order->getStateExpires() <= new \DateTime('now');
        $orderHasInvalidStatus = $order->getStatus() !== Order::STATUS_IN_PROGRESS;

        $orderNoLongerValid = $orderHasInvalidStatus || $expirationDatePastDue;

        return $orderNoLongerValid;
    }

    /**
     * Validates Order for Invoice printing by given token
     *
     * @param string $token
     * @return bool
     */
    public function isOrderValidForInvoice($token) {
        $order = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Order')
            ->findOneBy(
                array(
                    'token' => $token,
                )
            );
        if (empty($order)) {
            return false;
        }
        if (
            $order->getStatus() !== Order::STATUS_AWAITING_PAYMENT
            && $order->getStatus() !== Order::STATUS_DONE
        ) {
            return false;
        }
        if ($order->getInvoice() === null) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the order is done or not
     *
     * @param Order $order
     * @return bool
     */
    public function isOrderDone(Order $order)
    {
        if ($order->getStatus() === Order::STATUS_DONE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets Twig template name
     *
     * @param $type
     *
     * @return string
     */
    public function getPrintTemplate($type) {
        if ($type == TwigTemplateManager::TYPE_INVOICE_FINAL) {
            return self::INVOICE_FINAL_TEMPLATE;
        }
        if ($type == TwigTemplateManager::TYPE_INVOICE_PROFORMA) {
            return self::INVOICE_PROFORMA_TEMPLATE;
        }
    }

    /**
     * @param Order $order
     * @return self
     */
    public function updateTicketStatus(Order $order)
    {
        /** @var Ticket[] $orderTickets */
        $orderTickets = $this->entityManager
            ->getRepository('EvpTicketBundle:Ticket')
            ->getAllByOrder($order);

        foreach ($orderTickets as $ticket) {
            $ticket->setStatus(Ticket::STATUS_UNUSED);
            $this->entityManager->persist($ticket);
        }
        $this->updateOrderDiscountAmount($order, $orderTickets);
        $this->entityManager->flush();
        return $this;
    }

    /**
     * @param Order    $order
     * @param Ticket[] $tickets
     */
    protected function updateOrderDiscountAmount(Order $order, $tickets)
    {
        $originalPrice = 0;
        foreach ($tickets as $ticket) {
            $originalPrice += $ticket->getTicketType()->getPrice();
        }
        $discountAmount = $originalPrice - $order->getOrderPrice();
        $order->setDiscountAmount($discountAmount);
    }

    /**
     * Updates Seat status
     *
     * @param Order $order
     * @return self
     */
    public function updateSeatStatus(Order $order) {
        $orderTickets = $this->entityManager
            ->getRepository('EvpTicketBundle:Ticket')
            ->getAllByOrder($order);

        foreach ($orderTickets as $ticket) {
            $seat = $ticket->getSeat();
            if (!empty($seat)) {
                $seat->setStatus(Matrix::STATUS_TAKEN);
                $this->entityManager->persist($seat);
            }
        }
        return $this;
    }

    /**
     * @param Order $order
     *
     * @return self
     */
    public function updateInvoice(Order $order)
    {
        if ($order->getInvoiceRequired()) {
            $number = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Step\InvoiceDetails')
                ->getNextInvoiceNumber();

            $invoice = $order->getInvoice();
            $invoice->setNumber($number);

            $this->entityManager->flush();
        }

        return $this;
    }

    /**
     * Modifies or creates new OrderDetail
     *
     * @param TicketType $ticketType
     * @param int $count
     *
     * @return string
     */
    public function modifyOrderDetailsCountForTicketType(TicketType $ticketType, $count)
    {
        $currentUser = $this->userSession->getUserForThisSession();
        $eventId = $this->session->get($this->eventSessionKey);
        $event = $this->entityManager->getRepository('EvpTicketBundle:Event')
            ->findBy(array(
                    'id' => $eventId,
                ));

        $orderDetail = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findOneBy(array(
                    'ticketType' => $ticketType,
                    'user' => $currentUser,
                    'event' => $event,
                ));
        if (empty($orderDetail)) {
            $orderDetail = $this->createNewOrderDetail($currentUser, $ticketType, $count);
            return self::STATUS_ORDER_DETAILS_OK;
        }
        $validationResult = $this->validateCountLimitsForTicketType($ticketType, $count, $orderDetail);
        if ($validationResult !== true) {
            return $validationResult;
        }
        if ($orderDetail->getTicketsCount() == $count) {
            return self::STATUS_ORDER_DETAILS_OK;
        }
        $orderDetail->setTicketsCount($count);
        $this->entityManager->persist($orderDetail);
        $this->entityManager->flush();

        return self::STATUS_ORDER_DETAILS_OK;
    }

    /**
     * @param Order $order
     * @return self
     */
    public function updateOrderStatus(Order $order)
    {
        $order->setStatus(Order::STATUS_DONE);
        $order->setDateFinished(new \DateTime);
        $order->setTestMode($this->payseraTest);

        $this->entityManager->persist($order);
        $this->entityManager->flush();
        return $this;
    }

    /**
     * Gets list of fieldRecords for print in Tickets
     *
     * @param User $user
     *
     * @return array
     */
    public function getOrderRecordsByUser(User $user)
    {
        return $this->entityManager->getRepository('EvpTicketBundle:Form\FieldRecord')
            ->getRecordsForOrderByUser($user);
    }

    /**
     * Gets existing or creates new Order for User by optional status
     *
     * @param User   $user
     *
     * @param string $status
     *
     * @return Order
     */
    public function getOrderForUser(User $user, $status = Order::STATUS_IN_PROGRESS)
    {
        $order = $this->entityManager->getRepository('EvpTicketBundle:Order')
            ->findOneBy(array(
                    'user' => $user,
                    'status' => $status,
                ));
        if (empty($order)) {
            $order = $this->createFromUser($user);
        }
        return $order;
    }

    /**
     * Creates new OrderDetails based on provided User, TicketType & ticket count
     *
     * @param User       $user
     * @param TicketType $ticketType
     * @param            $count
     *
     * @return OrderDetails
     */
    private function createNewOrderDetail(User $user, TicketType $ticketType, $count)
    {
        $order = $this->getOrderForUser($user);
        $orderDetail = new OrderDetails;
        $orderDetail
            ->setOrder($order)
            ->setEvent($ticketType->getEvent())
            ->setTicketsCount($count)
            ->setUser($user)
            ->setTicketType($ticketType);

        $this->entityManager->persist($orderDetail);
        $this->entityManager->flush();
        return $orderDetail;
    }

    /**
     * Validates the given count against set-up validators for given TicketType
     *
     * @param TicketType                                        $ticketType
     * @param                                                   $count
     * @param \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails $orderDetail
     *
     * @return bool|string
     */
    private function validateCountLimitsForTicketType(TicketType $ticketType, $count, OrderDetails $orderDetail = null)
    {
        $freeTickets = $this->entityManager->getRepository('EvpTicketBundle:TicketType')
            ->getAvailableCountByEventAndTicketType($ticketType->getEvent(), $ticketType);

        if (!empty($orderDetail)) {
            if ($orderDetail->getTicketsCount() >= $count) {
                return true;
            } else {
                $count -= $orderDetail->getTicketsCount();
            }
        }
        if ($count < 0) {
            return self::ORDER_DETAILS_VALIDATION_NON_ZERO;
        }
        if ($count > $freeTickets) {
            return self::ORDER_DETAILS_VALIDATION_NOT_AVAILABLE_COUNT;
        }
        if (!empty($orderDetail)) {
            $count += $orderDetail->getTicketsCount();
        }
        if ($count > $ticketType->getEvent()->getEventType()->getMaxTicketsPerUser()) {
            return self::ORDER_DETAILS_VALIDATION_MAX_LIMIT_PER_USER;
        }
        return true;
    }
}
