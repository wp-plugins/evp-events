<?php
namespace Evp\Bundle\TicketBundle\Entity\Dynamic;


use Evp\Bundle\TicketBundle\Entity\Form\EventTypeFieldSchema;
use Evp\Bundle\TicketBundle\Entity\Form\FieldSchema;

/**
 * Class Entity
 * @package Evp\Bundle\TicketBundle\Entity\Dynamic
 * @author d.glezeris
 */
class Entity
{
    /**
     * @param EventTypeFieldSchema[] $parameters
     * @param bool $edit
     */
    public function __construct($parameters, $edit=false)
    {
        if (empty($parameters)) {
            return;
        }

        if ($edit==false) {
            foreach ($parameters as $value) {
                if (is_array($value)) {
                    $this->{$value['name']} = $value['value'];
                    continue;
                }

                $name = $value->getFieldSchema()->getName();
                $this->{$name} = "";
            }
        }
    }
}
