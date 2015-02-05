<?php
namespace Evp\Bundle\TicketMaintenanceBundle\Twig;


/**
 * Class IsNumericExtension
 * @package Evp\Bundle\TicketBundle\Twig
 *
 * Adds a 'is numeric' test
 */
class IsNumericExtension extends \Twig_Extension {
    /**
     * @return array
     */
    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('numeric', function ($value) {
                return is_numeric($value);
            })
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tickets.is_numeric_extension';
    }
}