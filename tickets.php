<?php
/*
    Plugin Name: Paysera Tickets
    Plugin URI: http://paysera.com
    Description: Allows creation of events where tickets/seat selections are required. Read the readme.txt for more details.
    Version: 1.0
    Author: evp.lt
    Author URI: http://evp.lt
*/

try {
    require_once __DIR__ . '/vendor/autoload.php';

    /* Plugin configuration */
    require_once __DIR__ . '/tickets/config.php';

    /* Let symfony handle the route */
    require_once __DIR__ . '/tickets/routing.php';

    /* Adds menu links to wordpress  */
    require_once __DIR__ . '/tickets/menu.php';
} catch(Exception $ex) {
    error_log($ex->getMessage());
}

register_activation_hook(__FILE__, 'tickets_activate');
function tickets_activate()
{
    try {
        $environment = new \Evp\Bundle\TicketMaintenanceBundle\Services\SystemEnvironment;
        $environment
            ->setRootDir(__DIR__ . '/app')
            ->enablePlugin();
    } catch(Exception $ex) {
        error_log($ex->getMessage());
    }
}

register_deactivation_hook(__FILE__, 'tickets_deactivate');
function tickets_deactivate()
{
    try {
        $environment = new \Evp\Bundle\TicketMaintenanceBundle\Services\SystemEnvironment;
        $environment
            ->setRootDir(__DIR__ . '/app')
        ;
    } catch(Exception $ex) {
        error_log($ex->getMessage());
    }
}

register_uninstall_hook(__FILE__, 'tickets_uninstall');
function tickets_uninstall()
{
    try {
        $environment = new \Evp\Bundle\TicketMaintenanceBundle\Services\SystemEnvironment;
        $environment
            ->setRootDir(__DIR__ . '/app')
        ;
    } catch(Exception $ex) {
        error_log($ex->getMessage());
    }
}
