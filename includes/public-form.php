<?php

defined('ABSPATH') || exit;

function snr_render_reservation_form()
{
    ob_start();
    $status = sanitize_key($_GET['snr_status'] ?? '');

    $slots = snr_get_time_slots();
    $service_labels = array(
        'lunch' => 'Comida',
        'dinner' => 'Cena',
    );

    if ('success' === $status) {
        ?>
        <div class="snr-confirmation" role="status">
            <h2>¡Reserva confirmada!</h2>
            <p>Te hemos enviado un email con los detalles de tu reserva.</p>
            <p>¡Te esperamos!</p>
            <p><a href="<?php echo esc_url(remove_query_arg('snr_status')); ?>">Hacer otra reserva</a></p>
        </div>
        <?php
        return ob_get_clean();
    }
    ?>
    <?php if ('full' === $status): ?>
        <p class="snr-message snr-message--error">Lo sentimos, no hay mesas disponibles para esa fecha y hora. Prueba con otro
            horario.</p>
    <?php endif; ?>
    <?php if ('error' === $status): ?>
        <p class="snr-message snr-message--error">Ha habido un problema al guardar tu reserva. Inténtalo de nuevo o llámanos.
        </p>
    <?php endif; ?>
    <?php if ('closed' === $status): ?>
        <p class="snr-message snr-message--error">Lo sentimos, el restaurante está cerrado en esa fecha u horario. Por favor,
            elige otro día.
        </p>
    <?php endif; ?>

    <form class="snr-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="snr_submit_reservation">
        <?php wp_nonce_field('snr_submit_reservation'); ?>

        <h2 class="snr-form__title">Reserva tu mesa</h2>
        <p class="snr-form__intro">Te confirmamos al instante por email.</p>

        <fieldset class="snr-form__section">
            <legend class="snr-form__legend">Tu reserva</legend>

            <div class="snr-form__grid">
                <p class="snr-field">
                    <label for="snr_reservation_date">Fecha</label>
                    <input type="date" id="snr_reservation_date" name="reservation_date"
                        min="<?php echo esc_attr(current_time('Y-m-d')); ?>" required>
                </p>
                <p class="snr-field">
                    <label for="snr_party_size">Personas</label>
                    <input type="number" id="snr_party_size" name="party_size" min="1" max="6" required>
                </p>
                <p class="snr-field">
                    <label for="snr_high_chairs">Tronas</label>
                    <input type="number" id="snr_high_chairs" name="high_chairs" value="0" min="0" max="5">
                </p>
            </div>

            <fieldset class="snr-times">
                <legend class="snr-times__legend">Hora</legend>

                <?php foreach ($slots as $service => $times): ?>
                    <div class="snr-times__group">
                        <span class="snr-times__service"><?php echo esc_html($service_labels[$service]); ?></span>
                        <div class="snr-times__options">
                            <?php foreach ($times as $slot): ?>
                                <label class="snr-chip">
                                    <input type="radio" name="start_time" value="<?php echo esc_attr($slot); ?>" required>
                                    <span><?php echo esc_html($slot); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </fieldset>
        </fieldset>

        <fieldset class="snr-form__section">
            <legend class="snr-form__legend">Tus datos</legend>

            <p class="snr-field">
                <label for="snr_customer_name">Nombre</label>
                <input type="text" id="snr_customer_name" name="customer_name" maxlength="100" required>
            </p>
            <div class="snr-form__grid">
                <p class="snr-field"> <label for="snr_customer_phone">Teléfono</label>
                    <input type="tel" id="snr_customer_phone" name="customer_phone" maxlength="30" required>
                </p>
                <p class="snr-field">
                    <label for="snr_customer_email">Email</label>
                    <input type="email" id="snr_customer_email" name="customer_email" maxlength="100" required>
                </p>
            </div>

            <p class="snr-field"> <label for="snr_notes">Notas adicionales</label>
                <textarea id="snr_notes" name="notes" rows="4" placeholder="Alergias, celebraciones..."></textarea>
            </p>

        </fieldset>

        <button type="submit" class="snr-button">Reservar mesa</button>
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('sn_reservation_form', 'snr_render_reservation_form');

function snr_enqueue_public_assets()
{
    global $post;

    if (!is_singular() || !$post || !has_shortcode($post->post_content, 'sn_reservation_form')) {
        return;
    }

    wp_enqueue_style(
        'snr-public',
        SNR_PLUGIN_URL . 'assets/css/public.css',
        array(),
        SNR_VERSION
    );
}

add_action('wp_enqueue_scripts', 'snr_enqueue_public_assets');

function snr_handle_reservation_submit()
{
    // 1. Nonce
    check_admin_referer('snr_submit_reservation');

    // 2. Recoger y sanear
    $name = sanitize_text_field(wp_unslash($_POST['customer_name'] ?? ''));
    $phone = sanitize_text_field(wp_unslash($_POST['customer_phone'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['customer_email'] ?? ''));
    $party_size = absint($_POST['party_size'] ?? 0);
    $high_chairs = absint($_POST['high_chairs'] ?? 0);
    $date = sanitize_text_field(wp_unslash($_POST['reservation_date'] ?? ''));
    $time = sanitize_text_field(wp_unslash($_POST['start_time'] ?? ''));
    $notes = sanitize_textarea_field(wp_unslash($_POST['notes'] ?? ''));

    // 3. Datos de referencia para validar
    $slots = snr_get_time_slots();
    $allowed_times = array_merge($slots['lunch'], $slots['dinner']);
    $today = current_time('Y-m-d');

    // 4. Validar 
    if (
        $name === ''
        || $phone === ''
        || !is_email($email)
        || $party_size < 1
        || $party_size > 6
        || $high_chairs > 5
        || $date < $today
        || !in_array($time, $allowed_times, true)
    ) {
        wp_die('Datos no válidos');
    }

    $redirect = wp_get_referer() ? wp_get_referer() : home_url('/');

    // Restaurante cerrado en ese día o turno
    if (snr_is_blocked($date, $time)) {
        wp_safe_redirect(add_query_arg('snr_status', 'closed', $redirect));
        exit;
    }

    $end_time = snr_calculate_end_time($time);
    $table = snr_find_available_table($date, $time, $end_time, $party_size);

    // 7. Sin mesa libre: volver con aviso
    if (!$table) {
        wp_safe_redirect(add_query_arg('snr_status', 'full', $redirect));
        exit;
    }

    // 8. Guardar la reserva 
    global $wpdb;
    $inserted = $wpdb->insert(
        $wpdb->prefix . 'snr_reservations',
        array(
            'table_id' => $table->id,
            'customer_name' => $name,
            'customer_phone' => $phone,
            'customer_email' => $email,
            'party_size' => $party_size,
            'high_chairs' => $high_chairs,
            'reservation_date' => $date,
            'start_time' => $time,
            'end_time' => $end_time,
            'notes' => $notes,
        ),
        array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s'
        )
    );

    // 8a. Si no se pudo guardar, avisar al cliente
    if (false === $inserted) {
        wp_safe_redirect(add_query_arg('snr_status', 'error', $redirect));
        exit;
    }

    // 8b. Email de confirmación
    snr_send_confirmation_email($email, $name, $date, $time, $party_size, $table->name);

    // 9. Volver con confirmación
    wp_safe_redirect(add_query_arg('snr_status', 'success', $redirect));
    exit;
}

add_action('admin_post_snr_submit_reservation', 'snr_handle_reservation_submit');
add_action('admin_post_nopriv_snr_submit_reservation', 'snr_handle_reservation_submit');
