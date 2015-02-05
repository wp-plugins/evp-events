<?php

add_action('admin_menu', 'build_tickets_menu');

function build_tickets_menu()
{
    add_menu_page('Manage Paysera Tickets', 'Tickets', 'manage_options', 'tickets', 'tickets_dashboard', null, 100 );
    wp_register_script( 'events', plugins_url( '/resources/js/events.js', __FILE__ ) );
}

function tickets_dashboard()
{
    ?>

    <style>
        #evp-tickets-content {
            width: 95%;
            height: 800px;
            margin: 1em;
        }
    </style>
    <iframe onload="/*appendCssToFrame()*/" id="evp-tickets-content" src="<?php echo get_option('siteurl'); ?>/evp-tickets/admin/en/dashboard"></iframe>
<?php
}
