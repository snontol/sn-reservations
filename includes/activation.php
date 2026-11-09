<?php

defined( 'ABSPATH' ) || exit;

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

