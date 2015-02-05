<?php

namespace Evp\Bundle\TicketAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('EvpTicketAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
