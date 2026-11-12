<?php

defined('ABSPATH') || exit;

function snr_register_rest_routes() {
    register_rest_route(
        'snr/v1',
        '/dashboard',
        array(
            'methods'             => 'GET',
            'callback'            => 'snr_rest_get_dashboard',
            'permission_callback' => 'snr_rest_can_manage',
            'args'                => array(
                'date' => array(
                    'required'          => false,
                    'validate_callback' => function ( $value ) {
                        return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value );
                    },
                ),
            ),
        )
    );
}

add_action( 'rest_api_init', 'snr_register_rest_routes' );

function snr_rest_can_manage() {
    return current_user_can( 'manage_options' );
}

function snr_rest_get_dashboard( $request ) {
    global $wpdb;
    $date = $request->get_param( 'date' ) ?: current_time( 'Y-m-d' );

    $reservations_table = $wpdb->prefix . 'snr_reservations';
    $tables_table       = $wpdb->prefix . 'snr_tables';

    // 1. Reservas confirmadas del día, con el nombre de su mesa
    $reservations = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT r.*, t.name AS table_name
             FROM $reservations_table r
             LEFT JOIN $tables_table t ON r.table_id = t.id
             WHERE r.reservation_date = %s
               AND r.status = 'confirmed'
             ORDER BY r.start_time, t.name",
            $date
        )
    );

    // 2. Plazas totales: la suma de la capacidad de las mesas activas
    $capacity = (int) $wpdb->get_var( "SELECT SUM(capacity) FROM $tables_table WHERE is_active = 1" );

    // 3. Recorrer las reservas para calcular los totales 
    $guests        = 0;
    $high_chairs   = 0;
    $guests_by_service = array( 'lunch' => 0, 'dinner' => 0 );
    $upcoming      = array();

    foreach ( $reservations as $reservation ) {
       $guests += (int) $reservation->party_size;
       $high_chairs += (int) $reservation->high_chairs;
        $service = snr_get_service( $reservation->start_time );
        $guests_by_service[ $service ] += (int) $reservation->party_size;

            $upcoming[] = array(
            'time'        => substr( $reservation->start_time, 0, 5 ),
            'table'       => $reservation->table_name,
            'name' => $reservation->customer_name,
            'party_size' => (int) $reservation->party_size,
            'high_chairs' => (int) $reservation->high_chairs,
            'notes' => $reservation->notes,    
        );
    }

    // 4. ¿Está bloqueado cada turno?
    $slots          = snr_get_time_slots();
    $lunch_blocked  = snr_is_blocked( $date, $slots['lunch'][0] );
    $dinner_blocked = snr_is_blocked( $date, $slots['dinner'][0] );

    // 5. Ocupación del día, solo sobre los turnos abiertos
        $open_services = 2;
    if ( $lunch_blocked ) {
        $open_services--;
    }
    if ( $dinner_blocked ) {
        $open_services--;
    }
    
    $available = $capacity * $open_services;
    $occupancy = $available > 0 ? (int) round( $guests / $available * 100 ) : 0;
    
    // 6. Respuesta según el contrato
    return rest_ensure_response(
        array(
            'date'     => $date,
            'summary'  => array(
                'reservations' => count( $reservations ),
                'guests'       => $guests,
                'high_chairs'  => $high_chairs,
                'occupancy'    => $occupancy,
            ),
            'services' => array(
                'lunch'  => array(
                    'guests'   => $guests_by_service['lunch'],
                    'capacity' => $capacity,
                    'blocked'  => $lunch_blocked,
                ),
                'dinner' => array(
                    'guests'   => $guests_by_service['dinner'],
                    'capacity' => $capacity,
                    'blocked'  => $dinner_blocked,
                ),
            ),
            'upcoming' => $upcoming,
        )
    );
}