<?php

defined('ABSPATH') || exit;

function snr_render_admin_page()
{
    global $wpdb;

    // 1. Fecha a mostrar: la del filtro o, si no hay, hoy
    $date = sanitize_text_field(wp_unslash($_GET['snr_date'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = current_time('Y-m-d');
    }

    // 2. Reservas de ese día, con el nombre de su mesa
    $reservations_table = $wpdb->prefix . 'snr_reservations';
    $tables_table = $wpdb->prefix . 'snr_tables';

    $reservations = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT r.*, t.name AS table_name
             FROM $reservations_table r
             LEFT JOIN $tables_table t ON r.table_id = t.id
             WHERE r.reservation_date = %s
             ORDER BY r.start_time, t.name",
            $date
        )
    );

    echo '<div class="wrap"><h1>Reservas</h1>';
    $test_url = add_query_arg('_wpnonce', wp_create_nonce('wp_rest'), rest_url('snr/v1/dashboard'));

    // 3. Filtro de fecha
    ?>
    <form method="get">
        <input type="hidden" name="page" value="sn-reservations">
        <label for="snr_date">Día:</label>
        <input type="date" id="snr_date" name="snr_date" value="<?php echo esc_attr($date); ?>">
        <button type="submit" class="button">Ver</button>
    </form>
    <?php

    // 4. La tabla de reservas
    if (empty($reservations)) {
        echo '<p>No hay reservas para este día.</p>';
    } else {
        echo '<table class="widefat striped">';

        // Cabecera: un <th> por cada columna
        echo '<thead><tr>';
        echo '<th>Hora</th>';
        echo '<th>Mesa</th>';
        echo '<th>Cliente</th>';
        echo '<th>Personas</th>';
        echo '<th>Tronas</th>';
        echo '<th>Teléfono</th>';
        echo '<th>Notas</th>';
        echo '<th>Estado</th>';
        echo '<th>Acciones</th>';
        echo '</tr></thead>';

        echo '<tbody>';
        foreach ($reservations as $reservation) {
            echo '<tr>';
            echo '<td>' . esc_html(substr($reservation->start_time, 0, 5)) . '</td>';
            echo '<td>' . esc_html($reservation->table_name) . '</td>';
            echo '<td>' . esc_html($reservation->customer_name) . '</td>';
            echo '<td>' . esc_html($reservation->party_size) . '</td>';
            echo '<td>' . esc_html($reservation->high_chairs) . '</td>';
            echo '<td>' . esc_html($reservation->customer_phone) . '</td>';
            echo '<td>' . esc_html($reservation->notes) . '</td>';
            echo '<td>' . ('confirmed' === $reservation->status ? 'Confirmada' : 'Cancelada') . '</td>';

            if ('confirmed' === $reservation->status) {
                ?>
                <td>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="snr_cancel_reservation">
                        <input type="hidden" name="reservation_id" value="<?php echo esc_attr($reservation->id); ?>">
                        <input type="hidden" name="snr_date" value="<?php echo esc_attr($date); ?>">
                        <?php wp_nonce_field('snr_cancel_reservation'); ?>
                        <button type="submit" class="button"
                            onclick="return confirm('¿Seguro que quieres cancelar esta reserva?');">Cancelar</button>
                    </form>
                </td>
                <?php
            } else {
                echo '<td>—</td>';
            }

            echo '</tr>';
        }
        echo '</tbody>';

        echo '</table>';
    }

    echo '</div>';
}

function snr_handle_cancel_reservation()
{
    // 1. Permisos: solo administradores

    if (!current_user_can('manage_options')) {
        wp_die('No tienes permisos para hacer esto.');
    }

    // 2. Nonce con 'snr_cancel_reservation'

    check_admin_referer('snr_cancel_reservation');

    // 3. Recoger y sanear:

    $reservation_id = absint($_POST['reservation_id'] ?? 0);
    $date = sanitize_text_field(wp_unslash($_POST['snr_date'] ?? ''));

    // 4. Validar:

    if ($reservation_id < 1) {
        wp_die('Datos no válidos');
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = current_time('Y-m-d');
    }

    // 5. Actualizar con $wpdb->update (recuerda global $wpdb):

    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'snr_reservations', // tabla
        array('status' => 'cancelled'),     // qué cambiar
        array('id' => $reservation_id),     // en qué fila (el WHERE)
        array('%s'),                        // formato de lo que cambias
        array('%d')                         // formato del WHERE
    );

    // 6. Volver a la página de reservas del mismo día
    wp_safe_redirect(add_query_arg('snr_date', $date, admin_url('admin.php?page=sn-reservations')));
    exit;
}

// 7. Conectar con el hook admin_post_snr_cancel_reservation
add_action('admin_post_snr_cancel_reservation', 'snr_handle_cancel_reservation');
