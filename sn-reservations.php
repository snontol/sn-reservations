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

defined('ABSPATH') || exit;

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

function snr_render_tables_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'snr_tables';
    $tables     = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id");

    echo '<div class="wrap"><h1>Mesas</h1>';
    echo '<table class="widefat striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Nombre</th>';
    echo '<th>Capacidad</th>';
    echo '<th>Activa</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($tables as $table) {
    echo '<tr>';
    echo '<td>' . esc_html($table->name) . '</td>';
    echo '<td>' . esc_html($table->capacity) . '</td>';
    echo '<td>' . ($table->is_active ? 'Sí' : 'No') . '</td>';
    echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

function snr_activate()
{
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

    $reservations_table = $wpdb->prefix . 'snr_reservations';
    $sql_reservations = "CREATE TABLE $reservations_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        table_id bigint(20) unsigned NOT NULL,
        customer_name varchar(100) NOT NULL,
        customer_phone varchar(30) NOT NULL,
        customer_email varchar(100) NOT NULL,
        party_size tinyint(3) unsigned NOT NULL,
        high_chairs tinyint(3) unsigned NOT NULL DEFAULT 0,
        reservation_date date NOT NULL,
        start_time time NOT NULL,
        end_time time NOT NULL,
        notes text,
        status varchar(20) NOT NULL DEFAULT 'confirmed',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY reservation_date (reservation_date)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_tables);
    dbDelta($sql_reservations);
}

register_activation_hook(__FILE__, 'snr_activate');
