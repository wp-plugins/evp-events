<?php
/**
 * Specific Acc action for Order
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\OrderMenu;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionInterface;
use Evp\Bundle\TicketBundle\Entity\Order;
use Evp\Bundle\TicketBundle\Entity\Step\OrderDetails;
use Evp\Bundle\TicketBundle\Entity\Ticket;
use Evp\Bundle\TicketBundle\Entity\TicketType;
use Evp\Bundle\TicketBundle\Entity\User;
use Evp\Bundle\TicketBundle\Service\OrderManager;
use Evp\Bundle\TicketBundle\Service\TicketManager;

/**
 * Class AddAction
 */
class AddAction extends ActionAbstract implements ActionInterface {

    const ROUTER_MAILER = 'evp_send_tickets';

    /**
     * @var string
     */
    protected $actionName = 'add';

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_REGULAR;

    /**
     * @var string
     */
    private $orderToken;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\OrderManager
     */
    private $orderManager;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\TicketManager
     */
    private $ticketManager;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Event
     */
    private $event;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\User
     */
    private $user;

    /**
     * @var \Evp\Bundle\TicketBundle\Entity\Order
     */
    private $order;

    /**
     * @var object
     */
    private $entityObj;

    /**
     * Sets necessary Managers
     *
     * @param \Evp\Bundle\TicketBundle\Service\OrderManager $orderManager
     * @param \Evp\Bundle\TicketBundle\Service\TicketManager $ticketManager
     */
    public function setManagers(
        OrderManager $orderManager,
        TicketManager $ticketManager
    ) {
        $this->orderManager = $orderManager;
        $this->ticketManager = $ticketManager;
    }

    /**
     * Sets Parameters for Action
     *
     * @param array $params
     * @return self
     */
    public function setParameters($params) {
        $this->fqcn = $params['fqcn'];
        $this->form = $params['form'];
        $this->request = $params['request'];
        return $this;
    }

    /**
     * Returns the Response type
     *
     * @return string
     */
    public function getResponseType() {
        $this->submitForm(false);
        return $this->responseType;
    }

    /**
     * Gets the current Action Template for Twig
     *
     * @return string
     */
    public function getResponseName() {
        if ($this->responseType == self::RESPONSE_REGULAR) {
            return $this->templates[$this->actionName];
        }
        if ($this->responseType == self::RESPONSE_REDIRECT) {
            return self::ROUTER_MAILER;
        }
    }

    /**
     * Builds template parameters by FQCN
     *
     * @return array
     */
    public function buildResponseParameters() {
        $this->logger->debug('Got User request for action \'' .$this->actionName .'\'');
        $result = $this->submitForm();
        if ($result !== true) {
            return array(
                'form' => $result,
            );
        }
        else {
            return array(
                'token' => $this->orderToken,
                '_locale' => $this->event->getDefaultLocale(),
            );
        }
    }

    /**
     * Submits the form and flushes the Entity
     *
     * @param bool $flush
     * @return bool
     */
    private function submitForm($flush = true) {
        $entityObj = null;
        if (empty($this->targetId)) {
            $entityObj = new $this->fqcn;
        } else {
            $entityObj = $this->entityManager->getRepository($this->fqcn)
                ->findOneBy(
                    array(
                        'id' => $this->targetId,
                    )
                );
        }
        $form = $this->formFactory->create($this->form, $entityObj);
        $ticketTypes = $this->request->get($this->form->getName());
        $form->handleRequest($this->request);
        if ($form->isValid() || !empty($ticketTypes)) {
            if ($flush) {
                $ticketTypes = $ticketTypes['ticketTypes'];
                $this->entityObj = $entityObj;
                $this->user = new User;
                $this->user->setDateCreated(new \DateTime);
                $this->user->setEmail($this->entityObj->getUser());
                $this->entityManager->persist($this->user);
                $this->entityManager->flush();
                $this->event = $this->entityObj->getEvent();
                $this->order = $this->orderManager->createFromUser($this->user);
                $this->user->setOrder($this->order);
                $this->entityManager->persist($this->user);
                $this->entityManager->flush();

                $this->createTickets($ticketTypes);

                $this->order->setEvent($this->event);
                $this->order->setPaymentType('generated_manually');
                $this->order->setDateFinished(new \DateTime);
                $this->order->setStatus(Order::STATUS_DONE);
                $this->orderManager->updateTicketCountAndPrice($this->order);
                $this->ticketManager->updateTicketStatusByOrder($this->order, Ticket::STATUS_UNUSED);

                $this->orderToken = $this->order->getToken();
            }
            $this->responseType = self::RESPONSE_REDIRECT;
            return true;
        }
        $this->responseType = self::RESPONSE_REGULAR;
        return $form->createView();
    }

    /**
     * Creates the requested Tickets & orderDetails
     *
     * @param array $ticketTypes
     */
    private function createTickets($ticketTypes) {
        $types = $ticketTypes['ticketType'];
        $counts = $ticketTypes['ticketsCount'];

        if (!is_array($types)) {
            $types = array($types);
        }
        if (!is_array($counts)) {
            $counts = array($counts);
        }

        foreach ($types as $key => $typeId) {
            $ticketType = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\TicketType')
                ->findOneBy(
                    array(
                        'id' => $typeId,
                        'event' => $this->event,
                    )
                );
            $orderDetails = new OrderDetails;
            $orderDetails
                ->setUser($this->user)
                ->setOrder($this->order)
                ->setEvent($this->event)
                ->setTicketType($ticketType)
                ->setTicketsCount($counts[$key]);
            $this->entityManager->persist($orderDetails);
            $this->entityManager->flush();

            $this->ticketManager->createTickets(
                $this->event,
                $ticketType,
                $orderDetails,
                $this->user,
                $counts[$key]
            );
        }
    }
}
