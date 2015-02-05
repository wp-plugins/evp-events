<?php
/**
 * SeatSelection step from multi-step forms
 * Enables user to select seat in Event.
 * Replaces TicketTypeSelection step
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step;

use Doctrine\ORM\EntityManager;
use Evp\Bundle\TicketBundle\Entity\Step\InvoiceDetails;
use Evp\Bundle\TicketBundle\Service\PaymentManager;
use Evp\Bundle\TicketBundle\Service\TicketManager;
use Evp\Bundle\TicketBundle\Step\Forms\InvoiceDetailsFillForm;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SeatSelection
 */
class SeatSelection extends StepAbstract implements StepInterface
{
    const STEP_SEAT_SELECTION = 'seat_selection';

    /**
     * @var string twig template
     */
    private $template;

    /**
     * @var \Symfony\Component\Form\AbstractType
     */
    protected $form;

    /**
     * @var \Evp\Bundle\TicketBundle\Service\UserSession
     */
    private $userSession;

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isStepCompleted()
    {
        $orderDetails = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findOneBy(array(
                    'user' => $this->user,
                    'event' => $this->event,
                ));
        return !empty($orderDetails);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getStepName()
    {
        return self::STEP_SEAT_SELECTION;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $params
     */
    public function setParams($params) {
        $this->template = $params['template'];
        $this->userSession = $params['user_session'];
    }

    /**
     * Step validation
     * @param \Symfony\Component\HttpFoundation\Request
     * @return boolean|array
     */
    public function validate(Request $request) {
        $orderDetails = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findBy(
                array(
                    'user' => $this->userSession->getUserForThisSession(),
                )
            );
        if (!empty($orderDetails)) {
            return null;
        }
        return true;
    }

    /**
     * Step rendering
     *
     * @return \Evp\Bundle\TicketBundle\Entity\Event
     */
    public function render() {
        $height = 0;
        $types = $this->event->getTicketTypes();
        foreach ($types as $type) {
            $rows = $type->getArea()->getRows();
            $height += $rows * $type->getArea()->getShapeOffsetY();
        }
        $height += $types[count($types)-1]->getArea()->getShapeOffsetY() / 2;

        $requestedOrderDetails = $this->entityManager
            ->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->getAllByUserAndEvent($this->user, $this->event);

        return array(
            'event' => $this->event,
            'holderHeight' => $height,
            'currentUser' => $this->userSession->getUserForThisSession(),
            'drawNumbers' => true,
            'requested' => $requestedOrderDetails,
        );
    }

    /**
     * Saving step data
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    public function save(Request $request) {
        $orderDetails = $this->entityManager->getRepository('Evp\Bundle\TicketBundle\Entity\Step\OrderDetails')
            ->findBy(
                array(
                    'user' => $this->userSession->getUserForThisSession(),
                )
            );
        foreach ($orderDetails as $detail) {
            $this->ticketManager->createTickets(
                $this->event,
                $detail->getTicketType(),
                $detail,
                $this->user,
                $detail->getTicketsCount(),
                TicketManager::RESERVATION_SHORT,
                true
            );
        }
        return true;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions($options) {
        $this->event = $options['event'];
        $this->user = $options['user'];
        return $this;
    }

    /**
     * Gets template form
     * @return string
     */
    public function getTemplate() {
        return $this->template;
    }
}
