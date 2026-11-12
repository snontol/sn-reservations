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
    $date = $request->get_param( 'date' );

       if ( empty( $date ) ) {
        $date = current_time( 'Y-m-d' );
    }

    return rest_ensure_response(
        array(
            'date'    => $date,
            'message' => 'La API funciona',
        )
    );
}