<?php
/**
 * Manages the Reporting System here
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\ReportingBundle\Service;

use Evp\Bundle\ReportingBundle\Entity\Report;
use Evp\Bundle\ReportingBundle\Exception\ReportFormNotFoundException;
use Evp\Bundle\ReportingBundle\Form\GenericReportForm;
use Evp\Bundle\ReportingBundle\Service\Report\ReportInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * Class ReportManager
 */
class ReportManager
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    private $formFactory;

    /**
     * @var ReportInterface[]
     */
    private $reports;

    /**
     * @var ReportInterface
     */
    private $currentReport;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Router
     */
    private $router;

    /**
     * @param LoggerInterface                     $log
     * @param \Symfony\Component\Form\FormFactory $formFactory
     * @param \Symfony\Component\Routing\Router   $router
     */
    public function __construct(
        LoggerInterface $log,
        FormFactory $formFactory,
        Router $router
    ) {
        $this->formFactory = $formFactory;
        $this->logger = $log;
        $this->router = $router;
    }

    /**
     * @param ReportInterface $report
     */
    public function addReport(ReportInterface $report)
    {
        $this->reports[] = $report;
    }

    /**
     * @param Request $request
     *
     * @throws \Evp\Bundle\ReportingBundle\Exception\ReportFormNotFoundException
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->logger->debug('Got Request for Reporting', $request->request->all());

        if ($request->request->count() !== 0) {
            if (!$request->request->has(GenericReportForm::FORM_NAME)) {
                $this->logger->debug('Form not found in Request', $request->request->all());
                throw new ReportFormNotFoundException('Required Form not found in Request');
            }
            $data = $request->request->get(GenericReportForm::FORM_NAME);
            foreach ($this->reports as $report) {
                if ($report->getName() === $data[GenericReportForm::REPORT_NAME]) {
                    $this->currentReport = $report;
                }
            }
        } else {
            $this->currentReport = reset($this->reports);
        }
        $this->currentReport->setRequest($request);
    }

    /**
     * @param string $name
     *
     * @return ReportInterface|null
     */
    public function getReportServiceByName($name)
    {
        foreach ($this->reports as $report) {
            if ($report->getName() === $name) {
                return $report;
            }
        }
        return null;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        $filters = $this->request->request->get(GenericReportForm::FORM_NAME);
        $report = null;
        if ($this->currentReport !== null && !empty($filters)) {
            $report = $this->currentReport->getReport($filters);
        }
        return $report;
    }

    /**
     * @return \Symfony\Component\Form\FormView
     */
    public function getFormView()
    {
        $fieldsUrl = $this->router->generate('report_form_part', array(), UrlGeneratorInterface::ABSOLUTE_URL);
        $reportForm = new GenericReportForm($this->getReportChoices(), $fieldsUrl);
        if ($this->currentReport !== null) {
            $reportForm->setReport($this->currentReport);
        }

        return $this->formFactory->create($reportForm)->handleRequest($this->request)->createView();
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return 'EvpReportingBundle:Report:genericReport.html.twig';
    }

    /**
     * @return array
     */
    private function getReportChoices()
    {
        $choices = array();
        foreach ($this->reports as $report) {
            $choices[$report->getName()] = $report->getName();
        }
        return $choices;
    }
}
