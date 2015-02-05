<?php
/**
 * FormInterface Symfony for Symfony forms
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketBundle\Step\Forms;

use Symfony\Component\Form\FormBuilder;

/**
 * Interface FormInterface
 */
interface FormInterface {

    /**
     * Gets current form
     * @param FormBuilder $formBuilder
     * @param mixed $options
     *
     * @return FormBuilder
     */
    function getForm(FormBuilder $formBuilder, $options);
}
