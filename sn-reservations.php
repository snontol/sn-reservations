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

function snr_activate() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $tables_table    = $wpdb->prefix . 'snr_tables';

    $sql_tables = "CREATE TABLE $tables_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        name varchar(50) NOT NULL,
        capacity tinyint(3) unsigned NOT NULL,
        is_active tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql_tables );
}

register_activation_hook( __FILE__, 'snr_activate' );

