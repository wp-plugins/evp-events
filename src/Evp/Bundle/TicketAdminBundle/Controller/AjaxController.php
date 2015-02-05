<?php

namespace Evp\Bundle\TicketAdminBundle\Controller;

use Evp\Bundle\TicketAdminBundle\Service\AjaxManager;
use Evp\Bundle\TicketAdminBundle\Service\TicketAdminGedmoAnnotationReader;
use Evp\Bundle\TicketMaintenanceBundle\Controller\NoPartialsController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends NoPartialsController
{

    /**
     * refresh entity based on input id and target locale
     *
     * @param string $entity
     * @param $id
     * @param $targetLocale
     * @return Response
     */

    public function refreshEntityAction($entity, $id, $targetLocale)
    {
        $ajaxManager = $this->get('evp.ticket_admin.service.ajax_manager');

        $entityClass = $entity ;
        $ajaxManager->setEntityClass($entityClass);
        $ajaxManager->setId($id);
        $ajaxManager->setTargetLocale($targetLocale);

        $translatedColumns = $ajaxManager->refreshEntityBasedOnTargetLocale($entity);

        $response = new Response(json_encode($translatedColumns), 200);
        return $this->appendNoPartials($response);
    }

    /**
     * Returns json response for ajax calls based in input params
     *
     * @param $serviceKey
     * @param $serviceRequestId
     * @param $serviceMethodKey
     * @return Response
     */
    public function getServiceResponseByIdAction($serviceKey, $serviceRequestId, $serviceMethodKey)
    {
        $ajaxMgr = $this->get('evp.ticket_admin.service.ajax_manager')
            ->setResponseScope($serviceKey)
            ->setResponseScopeKey($serviceRequestId)
            ->setResponseTarget($serviceMethodKey);

        $result = $ajaxMgr->getResult();

        $response = new Response($result, 200);
        return $this->appendNoPartials($response);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testMailSettingsAction(Request $request)
    {
        $settings = array();
        $data = $request->request;
        if ($data->has('admin_parameters_form_type')) {
            $data = $data->get('admin_parameters_form_type');
            $settings['transport'] = $data['mailer_transport'];
            $settings['host'] = $data['mailer_host'];
            $settings['user'] = $data['mailer_user'];
            $settings['password'] = $data['mailer_password'];
            $settings['auth_mode'] = $data['mailer_auth_mode'];
            $settings['port'] = $data['mailer_port'];
        } else {
            return $this->appendNoPartials(new Response('Wrong form submitted', 400));
        }

        $mailManager = $this->get('evp.service.mail_manager');
        $result = $mailManager->testSettings($settings);

        if ($result) {
            return $this->appendNoPartials(new Response('SUCCESS', 200));
        } else {
            return $this->appendNoPartials(new Response('FAILURE', 200));
        }
    }
}

