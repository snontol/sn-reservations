<?php

defined( 'ABSPATH' ) || exit;

function snr_calculate_end_time( $start_time ) {
    // 1. Separar horas y minutos
    list( $hours, $minutes ) = explode( ':', $start_time );

    // 2. Convertir a minutos desde medianoche 
    $start_minutes = (int) $hours * 60 + (int) $minutes;

    // 3. Duración: 90 en comida, 120 en cena 
    $duration = $start_minutes < 17 * 60 ? 90 : 120;

    // 4. Minutos de fin
    $end_minutes = $start_minutes + $duration;

    // 5. Volver a formato hora (HH:MM:SS)
    return sprintf( '%02d:%02d:00', intdiv( $end_minutes, 60 ), $end_minutes % 60 );
}

function snr_find_available_table( $date, $start_time, $end_time, $party_size ) {
    global $wpdb;
    $tables_table       = $wpdb->prefix . 'snr_tables';
    $reservations_table = $wpdb->prefix . 'snr_reservations';

    $sql = "SELECT * FROM $tables_table
            WHERE is_active = 1
              AND capacity >= %d
              AND id NOT IN (
                  SELECT table_id FROM $reservations_table
                  WHERE reservation_date = %s
                    AND status = 'confirmed'
                    AND start_time < %s
                    AND end_time > %s
              )
            ORDER BY capacity ASC, id ASC
            LIMIT 1";

    return $wpdb->get_row( $wpdb->prepare( $sql, $party_size, $date, $end_time, $start_time ) );
}

