<?php

defined( 'ABSPATH' ) || exit;

function snr_register_admin_menu()
{
    add_menu_page(
        'Reservas',
        'Reservas',
        'manage_options',
        'sn-reservations',
        'snr_render_admin_page',
        'dashicons-calendar-alt'
    );

    add_submenu_page(
        'sn-reservations',
        'Mesas',
        'Mesas',
        'manage_options',
        'snr-tables',
        'snr_render_tables_page'
    );
}

function snr_render_admin_page()
{
    echo '<div class="wrap"><h1>Reservas</h1></div>';
}

add_action('admin_menu', 'snr_register_admin_menu');