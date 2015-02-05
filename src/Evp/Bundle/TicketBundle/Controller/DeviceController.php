<?php
/**
 * DeviceController for device pairing stuff
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class DeviceController
 */
class DeviceController extends Controller {

    /**
     * Attaches device to Event by given Event token
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function attachAction($token) {
        $cookie = null;
        $ticketCheckerKey = $this->container->getParameter('evp.service.user_session.ticket_checker_key');
        $cookieValidity = $this->container->getParameter('evp.service.event_manager.cookie_validity');
        if ($this->getRequest()->cookies->has($ticketCheckerKey)) {
            $serialized = stripslashes($this->getRequest()->cookies->get($ticketCheckerKey));
            $allowedEventTokens = json_decode($serialized);
            if (!in_array($token, $allowedEventTokens)) {
                $allowedEventTokens[] = $token;
                $cookie = new Cookie(
                    $ticketCheckerKey,
                    json_encode($allowedEventTokens),
                    new \DateTime($cookieValidity)
                );
            } else {
                $cookie = new Cookie(
                    $ticketCheckerKey,
                    $serialized,
                    new \DateTime($cookieValidity)
                );
                $response = $this->render(
                    'EvpTicketBundle:GenericDisplay:displayInfo.html.twig',
                    array(
                        'message' => 'message.device.already_manager',
                    )
                );
                $response->headers->setCookie($cookie);
                $response->sendHeaders();

                return $response;
            }
        } else {
            $events = array($token);

            $cookie = new Cookie(
                $ticketCheckerKey,
                json_encode($events),
                new \DateTime($cookieValidity)
            );
        }
        $response = $this->render(
            'EvpTicketBundle:GenericDisplay:displayInfo.html.twig',
            array(
                'message' => 'message.device.attached_successfully',
            )
        );
        $response->headers->setCookie($cookie);
        $response->sendHeaders();

        return $response;
    }
} 
