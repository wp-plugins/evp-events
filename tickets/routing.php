<?php

add_action('template_redirect', 'performRoutingIfRouteIsFound');
add_action('admin_menu', 'performRoutingIfRouteIsFound');

function performRoutingIfRouteIsFound()
{
    require_once __DIR__ . '/../web/' . WP_CURRENT_ENV;
}