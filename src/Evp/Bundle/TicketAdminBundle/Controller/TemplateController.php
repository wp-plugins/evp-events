<?php
/**
 * ATemplate controller for rendering Custom templates to preview
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Controller;

use Evp\Bundle\TicketMaintenanceBundle\Controller\NoPartialsController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TemplateController
 * @package Evp\Bundle\TicketAdminBundle\Controller
 */
class TemplateController extends NoPartialsController {

    /**
     * Renders the template by name & optional params
     *
     * @param string $name
     * @param string $entity
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function previewAction($name, $entity, $token) {
        if (!empty($entity) && !empty($token)) {
            $entity = $this->getDoctrine()->getRepository('Evp\Bundle\TicketBundle\Entity\\' .ucfirst($entity))
                ->findOneBy(
                    array(
                        'token' => $token,
                    )
                );
        }

        $twig = $this->get('evp.service.database_twig_loader')
            ->setObject($entity)
            ->setType('custom')
            ->getTwig();

        $response = new Response(
            $twig->render(
                $name,
                array(
                    'entity' => $entity,
                )
            )
        );
        return $this->appendNoPartials($response);;
    }
} 
