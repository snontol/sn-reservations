<?php

defined( 'ABSPATH' ) || exit;

function snr_get_time_slots() {
    return array(
        'lunch'  => array( '13:00', '13:30', '14:00', '14:30' ),
        'dinner' => array( '20:30', '21:00', '21:30', '22:00' ),
    );
}

function snr_get_service( $time ) {
    list( $hours, $minutes ) = explode( ':', $time );
    $minutes_total = (int) $hours * 60 + (int) $minutes;

    // Devuelve 'lunch' si es antes de las 17:00, y 'dinner' si no (esto lo escribes tú)
    return $minutes_total < 17 * 60 ? 'lunch' : 'dinner';
}

function snr_calculate_end_time( $start_time ) {
    // 1. Separar horas y minutos
    list( $hours, $minutes ) = explode( ':', $start_time );

    // 2. Convertir a minutos desde medianoche 
    $start_minutes = (int) $hours * 60 + (int) $minutes;

    // 3. Duración: 90 en comida, 120 en cena 
    $duration = 'lunch' === snr_get_service( $start_time ) ? 90 : 120;

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

function snr_is_blocked( $date, $time ) {
    global $wpdb;
    $blocks_table = $wpdb->prefix . 'snr_blocks';
    $service      = snr_get_service( $time );

    $count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $blocks_table
             WHERE block_date = %s
               AND ( service = 'all' OR service = %s )",
            $date,
            $service
        )
    );

    return $count > 0;
}