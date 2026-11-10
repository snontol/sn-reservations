<?php

defined('ABSPATH') || exit;

function snr_send_confirmation_email($email, $name, $date, $time, $party_size, $table_name)
{
    $subject = 'Tu reserva está confirmada';

    $formatted_date = wp_date('d/m/Y', strtotime($date), new DateTimeZone('UTC'));
    $formatted_time = substr($time, 0, 5);

    // El mensaje 
    $message = 'Hola ' . $name . ",\n\n"
        . "Tu reserva está confirmada:\n\n"
        . 'Fecha: ' . $formatted_date . "\n"
        . 'Hora: ' . $formatted_time . "\n"
        . 'Número de personas: ' . $party_size . "\n"
        . 'Mesa: ' . $table_name . "\n\n"
        . "Si necesitas cambiar o cancelar tu reserva, llámanos.\n\n"
        . "¡Te esperamos!";

    return wp_mail($email, $subject, $message);
}