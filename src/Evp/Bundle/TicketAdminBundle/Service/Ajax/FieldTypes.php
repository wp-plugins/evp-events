<?php

namespace Evp\Bundle\TicketAdminBundle\Service\Ajax;


class FieldTypes extends AjaxAbstract implements AjaxInterface
{
    /**
     *  Holds fieldTypes.validators key from evp.ticket_admin.menu_supplemental_items collection
     */
    const VALIDATORS_KEY = 'fieldTypes.validators';

    /**
     *  Hold the collection for evp.ticket_admin.menu_supplemental_items['fieldTypes.validators']
     */
    private $validators;


    /**
     * Returns the validator for input field type e.g 'text' will return 'NotBlank'
     * $return['type'] = 'string' will indicate that it will populate a text field on client side.
     *
     * @return array
     */
    public function getResult()
    {
        $return = array();
        $data = array();

        $this->validators = $this->params[self::VALIDATORS_KEY];
        $data[] = $this->validators[strtolower($this->scopeId)];

        $return['type'] = 'string';
        $return['data'] = $data;

        return $return;

    }

}

