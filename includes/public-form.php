<?php

defined('ABSPATH') || exit;

function snr_render_reservation_form()
{
    ob_start();
?>
    <form class="snr-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="snr_submit_reservation">
        <?php wp_nonce_field('snr_submit_reservation'); ?>

        <p>
            <label for="snr_customer_name">Nombre</label>
            <input type="text" id="snr_customer_name" name="customer_name" maxlength="100" required>
        </p>
        <p> <label for="snr_customer_phone">Teléfono</label>
            <input type="tel" id="snr_customer_phone" name="customer_phone" maxlength="30" required>
        </p>
        <p>
            <label for="snr_customer_email">Email</label>
            <input type="email" id="snr_customer_email" name="customer_email" maxlength="100" required>
        </p>
        <p> <label for="snr_party_size">Número de personas</label>
            <input type="number" id="snr_party_size" name="party_size" min="1" max="6" required>
        </p>
        <p> <label for="snr_high_chairs">Número de tronas</label>
            <input type="number" id="snr_high_chairs" name="high_chairs" value="0" min="0" max="5">
        </p>
        <p> <label for="snr_reservation_date">Fecha de la reserva</label>
            <input type="date" id="snr_reservation_date" name="reservation_date" required>
        </p>
        <p> <label for="snr_start_time">Hora de inicio</label>

            <select id="snr_start_time" name="start_time" required>
                <option value="">Elige una hora</option>
                <optgroup label="Comida">
                    <option value="13:00">13:00</option>
                    <option value="13:30">13:30</option>
                    <option value="14:00">14:00</option>
                    <option value="14:30">14:30</option>
                </optgroup>
                <optgroup label="Cena">
                    <option value="20:30">20:30</option>
                    <option value="21:00">21:00</option>
                    <option value="21:30">21:30</option>
                    <option value="22:00">22:00</option>
                </optgroup>
            </select>
        </p>

        <p> <label for="snr_notes">Notas adicionales</label>
            <textarea id="snr_notes" name="notes" rows="4" placeholder="Alergias, celebraciones..."></textarea>
        </p>

        <p><button type="submit">Reservar</button></p>
    </form>
<?php
    return ob_get_clean();
}

add_shortcode('sn_reservation_form', 'snr_render_reservation_form');

function snr_handle_reservation_submit()
{
    // 1. Nonce
    check_admin_referer('snr_submit_reservation');

    // 2. Recoger y sanear
    $name        = sanitize_text_field(wp_unslash($_POST['customer_name'] ?? ''));
    $phone       = sanitize_text_field(wp_unslash($_POST['customer_phone'] ?? ''));
    $email       = sanitize_email(wp_unslash($_POST['customer_email'] ?? ''));
    $party_size  = absint($_POST['party_size'] ?? 0);
    $high_chairs = absint($_POST['high_chairs'] ?? 0);
    $date        = sanitize_text_field(wp_unslash($_POST['reservation_date'] ?? ''));
    $time        = sanitize_text_field(wp_unslash($_POST['start_time'] ?? ''));
    $notes       = sanitize_textarea_field(wp_unslash($_POST['notes'] ?? ''));

    // 3. Datos de referencia para validar
    $allowed_times = array('13:00', '13:30', '14:00', '14:30', '20:30', '21:00', '21:30', '22:00');
    $today         = current_time('Y-m-d');

    // 4. Validar 
    if ($name === '' 
    || $phone === '' 
    || ! is_email($email) 
    || $party_size < 1 
    || $party_size > 6 
    || $high_chairs > 5 
    || $date < $today 
    || !in_array($time, $allowed_times, true)) {
        wp_die('Datos no válidos');
    }

    // 5. Prueba temporal: mostrar los datos recibidos
    wp_die('Datos correctos: ' . esc_html($name) . ', ' . esc_html($date) . ' a las ' . esc_html($time));
}

add_action('admin_post_snr_submit_reservation', 'snr_handle_reservation_submit');
add_action('admin_post_nopriv_snr_submit_reservation', 'snr_handle_reservation_submit');
