<?php

namespace Evp\Bundle\TicketMaintenanceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class NoPartialsController extends RedirectController
{
    /**
     * Forwards the request to another controller.
     *
     * @param string $view
     * @param array $parameters
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        $response = parent::render($view, $parameters, $response);
        return $this->appendNoPartials($response);
    }
} 