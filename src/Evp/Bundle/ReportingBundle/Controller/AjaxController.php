<?php
/**
 * Ajax Controller for Reporting cases
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\ReportingBundle\Controller;

use Evp\Bundle\ReportingBundle\Form\GenericReportForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AjaxController
 * @package Evp\Bundle\ReportingBundle\Controller
 */
class AjaxController extends Controller
{
    /**
     * Gets the part of form for given Report & Event
     *
     * @param string $report
     * @param string $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formPartAction($report, $event)
    {
        $data = array(
            GenericReportForm::FORM_NAME => array(
                'event' => $event,
            )
        );
        $request = new Request(array(), $data);
        $reportManager = $this->get('evp_reporting.report_manager');
        $report = $reportManager->getReportServiceByName($report);
        $report->setRequest($request);

        $formBuilder = $this->createFormBuilder();
        $injectedFields = $report->getInjectedElementsNames();
        if (!empty($injectedFields)) {
            $report->injectFormElements($formBuilder);
        }

        $fields = '';
        foreach ($report->getInjectedElementsNames() as $element) {
            $formView = $formBuilder->get($element)->getForm()->createView();
            if (strpos($formView->vars['full_name'], '[]') === false) {
                $formView->vars['full_name'] = GenericReportForm::FORM_NAME .'[' .$formView->vars['name'] .']';
            } else {
                $formView->vars['full_name'] = GenericReportForm::FORM_NAME .'[' .$formView->vars['name'] .'][]';
            }
            $fields .= $this->get('templating')->render('EvpReportingBundle::formEcho.html.twig', array('form' => $formView));
        }

        return new Response($fields, 200, array('Content-Type' => 'text/plain'));
    }
} 
