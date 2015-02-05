<?php
/**
 * TicketManager for multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Service;

use Doctrine\ORM\EntityNotFoundException;
use Evp\Bundle\DeviceApiBundle\Entity\User\TicketExaminer;
use Evp\Bundle\TicketBundle\Entity\Event;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Evp\Bundle\TicketBundle\Entity\User;
use Evp\Bundle\TicketBundle\Entity\Order;
use Evp\Bundle\TicketBundle\Step\UserDetailsFill;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class TicketManager
 */
class TicketManager extends ManagerAbstract {

    const OFFLINE_SYNC_LIMIT = 100;
    const RESERVATION_SHORT = 'short';

    /**
     * @var string[]
     */
    private $reservation;

    /**
     * @var string[]
     */
    private $templates;

    /**
     * @var array
     */
    private $syncParams;

    /**
     * @var ParameterBag
     */
    private $syncFilters;

    /**
     * @var TicketTokenManager
     */
    private $tokenManager;


    /**
     * Sets reservations from Service parameters
     * @param string[] $reservation
     */
    public function setReservation($reservation) {
        $this->reservation = $reservation;
    }

    /**
     * Sets template names from Service parameters
     * @param string[] $templates
     */
    public function setTemplates($templates) {
        $this->templates = $templates;
    }

    /**
     * Sets the default offline sync params
     *
     * @param array $params
     */
    public function setSyncParams($params) {
        $this->syncParams = $params;
    }

    /**
     * @param TicketTokenManager $tokenManager
     */
    public function setTicketTokenManager(TicketTokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * Creates a number of tickets for particular Event & User by given TicketType
     * and sets reservation period.
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $event
     * @param \Evp\Bundle\TicketBundle\Entity\TicketType $ticketType
     * @param \Evp\Bundle\TicketBundle\Entity\Step\OrderDetails $orderDetails
     * @param \Evp\Bundle\TicketBundle\Entity\User $user
     * @param int $count
     * @param string $reservation
     * @param bool $assignSeats
     */
    public function createTickets(
        Event $event,
        TicketType $ticketType,
        OrderDetails $orderDetails,
        User $user,
        $count = 1,
        $reservation = self::RESERVATION_SHORT,
        $assignSeats = false
    ) {
        $orderDetails->getOrder()
            ->setStatus(Order::STATUS_IN_PROGRESS)
            ->setStateExpires(new \DateTime($this->reservation[$reservation]));

        $tickets = array();
        $count = intval($count);

        $seats = array();
        if ($assignSeats) {
            $seats = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Seat\Matrix')
                ->findBy(
                    array(
                        'orderDetails' => $orderDetails,
                        'area' => $ticketType->getArea(),
                    )
                );
        }
        for ($i = 0; $i < $count; $i++) {
            $ticket = new Ticket;
            $ticket->setEvent($event);
            $ticket->setTicketType($ticketType);
            $ticket->setPrice($ticketType->getPrice());
            $ticket->setDateCreated(new \DateTime);
            $ticket->setStatus(Ticket::STATUS_IDLE);
            $ticket->setUser($user);
            $ticket->setOrderDetails($orderDetails);
            $ticket->setToken($this->tokenManager->getTokenForTicketByEvent($ticket, $event));

            if (!empty($seats)) {
                $ticket->setSeat($seats[$i]);
            }
            $tickets[] = $ticket;
            $this->entityManager->persist($ticket);
            $this->entityManager->flush();
        }
        $user->setTickets($tickets);
        $this->entityManager->flush($user);
    }

    /**
     * Gets stdClass with FieldRecord & Schema for patricular Event
     *
     * @param \Evp\Bundle\TicketBundle\Entity\Event $event
     * @param \Evp\Bundle\TicketBundle\Entity\User $user
     * @param \stdClass $schemas
     *
     * @return \stdClass[]
     */
    public function getFilledTickets(Event $event, User $user, \stdClass $schemas = null) {
        $tickets = array();
        $filledTickets = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->getFilledTicketsByEventAndUser($event, $user);
        if (empty($schemas)) {
            $schemas = $this->prepareStdClass($event->getEventType()->getEventFieldSchemas(), true);
        }
        foreach ($filledTickets as $ticket) {
            $filledTicket = new \stdClass;
            $nameValuePair = array();
            foreach ($schemas as $name => $value) {
                $schema = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Form\FieldSchema')
                    ->findOneBy(array(
                        'name' => $name,
                        )
                    );
                $record = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Form\FieldRecord')
                    ->findOneBy(array(
                            'event' => $event,
                            'user' => $user,
                            'fieldSchema' => $schema,
                            'ticket' =>$ticket,
                        )
                    );
                $nameValuePair[$name]['label'] = $schema->getLabel();
                $nameValuePair[$name]['value'] = $record->getValue();
            }
            $filledTicket->schema = $nameValuePair;
            $filledTicket->ticketId = $ticket->getId();
            $filledTicket->event = $event->getName();
            $filledTicket->ticketType = $ticket->getTicketType()->getName();
            $tickets[] = $filledTicket;
        }
        return $tickets;
    }

    /**
     * Gets all FieldRecords by Ticket
     *
     * @param Ticket $ticket
     * @return \stdClass[]
     */
    public function getTicketFieldRecords(Ticket $ticket) {
        $records = array();
        $fieldRecords = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Form\FieldRecord')
            ->findBy(
                array(
                    'ticket' => $ticket,
                )
            );
        foreach ($fieldRecords as $key => $fieldRecord) {
            $records[$key]['label'] = $fieldRecord->getFieldSchema()->getLabel();
            $records[$key]['value'] = $fieldRecord->getValue();
            $records[$key]['name'] = $fieldRecord->getFieldSchema()->getName();
        }
        return $records;
    }

    /**
     * Removes FieldRecords by Event, User, Ticket
     *
     * @param \Evp\Bundle\TicketBundle\Entity\User $user
     * @param \Evp\Bundle\TicketBundle\Entity\Event $event
     * @param \Evp\Bundle\TicketBundle\Entity\Ticket $ticket
     */
    public function removeTicketFieldRecords(User $user, Event $event, Ticket $ticket) {
        $fieldRecords = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Form\FieldRecord')
            ->findBy(array(
                    'event' => $event,
                    'user' => $user,
                    'ticket' => $ticket,
                )
            );
        foreach ($fieldRecords as $record) {
            $this->entityManager->remove($record);
        }
        $this->entityManager->flush();
    }

    /**
     * Prepares custom stdClass for given fields from FieldSchema[]
     *
     * @param array $schemas
     * @param bool $includeHidden
     * @return \stdClass
     */
    private function prepareStdClass($schemas, $includeHidden = false) {
        $arr = array();
        foreach ($schemas as $schema) {
            $arr[$schema->getFieldSchema()->getName()] = null;
        }
        if ($includeHidden) {
            $arr[UserDetailsFill::TICKET_ID_HIDDEN_NAME] = null;
        }
        return (object)$arr;
    }

    /**
     * Validates ticket token for printing
     *
     * @param string $token
     * @return bool
     */
    public function validateTicket($token) {
        $ticket = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->findOneBy(array(
                    'token' => $token,
                )
            );
        if (empty($ticket)) {
            return false;
        }
        if ($ticket->getStatus() !== Ticket::STATUS_UNUSED) {
            return false;
        }
        if ($ticket->getEvent()->getDateEnds() <= new \DateTime) {
            return false;
        }
        return true;
    }

    /**
     * Updates Tickets by Order to given status
     * @param Order $order
     * @param string $status
     */
    public function updateTicketStatusByOrder(Order $order, $status = Ticket::STATUS_IDLE) {
        $tickets = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->getAllByOrder($order);
        foreach ($tickets as $ticket) {
            $ticket->setStatus($status);
            $ticket->setDateModified(new \DateTime);
            $this->entityManager->persist($ticket);
        }
        $this->entityManager->flush();
    }


    /**
     * The specified ticket is marked as used
     *
     * @param Ticket $ticket
     * @param TicketExaminer $examiner
     */
    public function markAsUsed(Ticket $ticket, TicketExaminer $examiner = null) {
        $ticket->setStatus(Ticket::STATUS_USED);
        $ticket->setTicketExaminer($examiner);
        $this->entityManager->flush($ticket);
    }

    /**
     * Gets Tickets for offline sync based on optional filters & status
     *
     * @param ParameterBag $filters
     * @param string $status
     * @return Ticket[]
     */
    public function getTicketsForOfflineSync(ParameterBag $filters, $status = Ticket::STATUS_UNUSED) {
        $this->logger->debug('Default filters will be used for Tickets with status \'' .$status .'\'', $this->syncParams);

        $this->syncFilters = clone $filters;
        $this->syncFilters->add(array('status' =>$status));

        return $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Ticket')
            ->getAllByFilters($this->syncFilters->all(), $this->syncParams);
    }

    /**
     * Gets the Event[] by previous Filters
     *
     * @return Event[]
     */
    public function getEventsBySyncFilters() {
        $starts = new \DateTime($this->syncParams['sync_depth_past']);
        $ends = new \DateTime($this->syncParams['sync_depth_future']);

        if (array_key_exists('event_from', $this->syncFilters->all())) {
            $starts->setTimestamp($this->syncFilters->get('event_from'));
        }
        if (array_key_exists('event_until', $this->syncFilters->all())) {
            $ends->setTimestamp($this->syncFilters->get('event_until'));
        }

        $events = $this->entityManager->getRepository('EvpTicketBundle:Event')
            ->getAllByDateStartsAndEnds($starts, $ends);
        return $events;
    }

    /**
     * Returns Ticket entity by it's token
     *
     * @param string $token
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @return Ticket
     */
    public function getTicketByToken($token) {
        $ticket = $this->entityManager->getRepository('EvpTicketBundle:Ticket')
            ->findOneBy(
                array(
                    'token' => $token,
                )
            );
        if (empty($ticket)) {
            throw new EntityNotFoundException;
        }
        return $ticket;
    }

    /**
     * Returns template name by type
     * @param string $type
     * @return string
     */
    public function getPrintTemplate($type = 'html') {
        return $this->templates[$type];
    }

    /**
     * Handles the difference in current Tickets count & new setting in OrderDetails
     *
     * @param OrderDetails $orderDetails
     */
    public function handleTicketsDifferenceByOrderDetails(OrderDetails $orderDetails)
    {
        $tickets = $this->entityManager->getRepository('EvpTicketBundle:Ticket')
            ->findBy(array(
                    'orderDetails' => $orderDetails,
                    'event' => $orderDetails->getEvent(),
                    'ticketType' => $orderDetails->getTicketType(),
                    'user' => $orderDetails->getUser(),
                ));
        $additionalCnt = $orderDetails->getTicketsCount() - count($tickets);
        $this->logger->debug('Handling Ticket difference for new OrderDetails', array($orderDetails, 'diff' => $additionalCnt));

        if ($additionalCnt < 0) {
            $toDelete = array_slice($tickets, $additionalCnt);
            foreach ($toDelete as $ticket) {
                $this->tokenManager->unbindTokenByTicket($ticket);
                $this->entityManager->remove($ticket);
            }
            $this->entityManager->flush();
        }
        if ($additionalCnt > 0) {
            $this->createTickets(
                $orderDetails->getEvent(),
                $orderDetails->getTicketType(),
                $orderDetails,
                $orderDetails->getUser(),
                $additionalCnt
            );
        }
    }
}
