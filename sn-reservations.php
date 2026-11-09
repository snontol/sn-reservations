<?php
/**
 * Plugin Name: SN Reservations
 * Description: Sistema de reservas para restaurantes con asignación automática de mesas, confirmación por email y panel de gestión.
 * Version: 0.1.0
 * Author: Samuel Nontol
 * Author URI: https://snontol.github.io/nontol.dev/
 * Text Domain: sn-reservations
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit; 

function snr_register_admin_menu() {
    add_menu_page(
        'Reservas',
        'Reservas',
        'manage_options',
        'sn-reservations',
        'snr_render_admin_page',
        'dashicons-calendar-alt'
    );
}

function snr_render_admin_page() {
    echo '<div class="wrap"><h1>Reservas</h1></div>';
}

add_action( 'admin_menu', 'snr_register_admin_menu' );