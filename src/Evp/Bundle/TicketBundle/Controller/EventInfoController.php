<?php
/**
 * EventInfoController for rendering Event(s) info
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EventInfoController
 */
class EventInfoController extends Controller {

    const ALL_EVENTS_TEMPLATE = 'EvpTicketBundle:EventInfo:all.html.twig';
    const EVENT_INFO_TEMPLATE = 'EvpTicketBundle:EventInfo:event.html.twig';
    const EVENT_INFO_INACTIVE_TEMPLATE = 'EvpTicketBundle:EventInfo:event_inactive.html.twig';

    /**
     * Renders all Events, based on Visibility parameter in DIC
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allAction() {
        $events = $this->get('evp.service.event_manager')->getAllEvents();

        return $this->render(self::ALL_EVENTS_TEMPLATE, array(
                'elements' => $events,
            )
        );
    }

    /**
     * Returns info about particular event
     * @param string $eventId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function eventAction($eventId) {
        $event = $this->get('evp.service.event_manager')->getEvent($eventId);
        if ($event !== null) {
            if (!$this->get('evp.service.step_manager')->isValidEvent($event)) {
                return $this->redirect(
                    $this->generateUrl('evp_ticket_event_info_inactive', array('eventId' => $eventId))
                );
            }

            return $this->render(self::EVENT_INFO_TEMPLATE, array(
                'event' => $event,
            ));
        }

        $this->get('logger')->warning(
            'Event not found by id. Giving Empty Response',
            array('id' => $eventId)
        );
        return new Response();
    }

    /**
     * Returns info about particular event
     * @param string $eventId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function eventInactiveAction($eventId) {
        $event = $this->get('evp.service.event_manager')->getEvent($eventId);
        if ($event !== null) {
            return $this->render(self::EVENT_INFO_INACTIVE_TEMPLATE, array(
                'event' => $event,
            ));
        }

        $this->get('logger')->warning(
            'Event not found by id. Giving Empty Response',
            array('id' => $eventId)
        );
        return new Response();
    }
}
