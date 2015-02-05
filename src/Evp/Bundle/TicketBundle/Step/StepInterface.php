<?php
/**
 * StepInterface for multi-step forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface StepInterface
 */
interface StepInterface
{
    /**
     * Step validation
     * Returns boolean true if no errors found in Request, but proceed not allowed
     * Returns null if no errors found and allowed to proceed
     * Returns array of errors when such found
     *
     * @param \Symfony\Component\HttpFoundation\Request
     * @return boolean|array|null
     */
    function validate(Request $request);

    /**
     * Step rendering
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function render();

    /**
     * Saving step data
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    function save(Request $request);

    /**
     * @param array $options
     */
    function setOptions($options);

    /**
     * Gets template form
     * @return string
     */
    function getTemplate();

    /**
     * Sets the parameters, required for current step
     * @param array $params
     */
    function setParams($params);

    /**
     * Checks if step has all required data persisted
     *
     * @return bool
     */
    function isStepCompleted();

    /**
     * Gets the current Step unique name
     *
     * @return string
     */
    function getStepName();
}
