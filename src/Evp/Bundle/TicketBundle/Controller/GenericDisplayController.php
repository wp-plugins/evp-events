<?php

namespace Evp\Bundle\TicketBundle\Controller;

use Evp\Bundle\TicketMaintenanceBundle\Controller\RedirectController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * Class GenericDisplayController
 * @package Evp\Bundle\TicketBundle\Controller
 *
 * This controller is the class where the user is redirected
 * after an error occurred
 */
class GenericDisplayController extends RedirectController
{
    /**
     * Displays Error message
     *
     * @param $message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayErrorAction($message)
    {
        $returnToList = false;
        if ($message == 'error.session_timeout') {
            $returnToList = true;
        }
        return $this->render('EvpTicketBundle:GenericDisplay:displayError.html.twig',
            array('message' => $message, 'returnToList' => $returnToList)
        );
    }

    /**
     * Displays Info page with given message
     *
     * @param string $message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayInfoAction($message)
    {
        if (base64_encode(base64_decode($message, true)) === $message) {
            $message = base64_decode($message);
        }
        $message = $this->appendAnchorIfUrl($message);
        return $this->render('EvpTicketBundle:GenericDisplay:displayInfo.html.twig',
            array('message' => $message)
        );
    }


    /**
     * Displays Info page with given message
     *
     * @param string $message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayInfoNoPartialsAction($message)
    {
        if (base64_encode(base64_decode($message, true)) === $message) {
            $message = base64_decode($message);
        }
        $message = $this->appendAnchorIfUrl($message);
        $response = $this->render('EvpTicketBundle:GenericDisplay:displayInfo.html.twig',
            array('message' => $message)
        );

        $response->headers->add(
            array(
                'no-partials' => 1,
                'content-type' => 'text/html'
            )
        );

        return $response;
    }

    /**
     * Appends <a>...</a> to $message if matches URL pattern
     *
     * @param string $message
     * @param string $target
     * @return string
     */
    private function appendAnchorIfUrl($message, $target = '_blank')
    {
        $hostName = $this->container->getParameter('server_hostname');
        if (strpos($message, $hostName) !== false) {
            return "<a href='{$message}' target='{$target}'>" .$message .'</a>';
        }
        return $message;
    }
}
