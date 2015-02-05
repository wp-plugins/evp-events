<?php

namespace Evp\Bundle\ReportingBundle\Service\Report;

use Evp\Bundle\ReportingBundle\Entity\Report;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface ReportInterface
 */
interface ReportInterface
{
    /**
     * Gets the name of current Report
     *
     * @return string
     */
    function getName();

    /**
     * Injects additional Form elements to Builder
     *
     * @param FormBuilderInterface $builder
     */
    function injectFormElements(FormBuilderInterface $builder);

    /**
     * Gets the data from current Report by given filters
     *
     * @param array $filters
     *
     * @return Report
     */
    function getReport(array $filters);

    /**
     * Store the Request
     *
     * @param Request $request
     *
     * @return ReportInterface
     */
    function setRequest(Request $request);

    /**
     * Gets the Names of injected Form Elements
     *
     * @return array
     */
    function getInjectedElementsNames();
} 
