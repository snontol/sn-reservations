<?php

defined('ABSPATH') || exit;

function snr_render_blocks_page()
{
    global $wpdb;
    $blocks_table = $wpdb->prefix . 'snr_blocks';
    $today = current_time('Y-m-d');

    $blocks = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $blocks_table WHERE block_date >= %s ORDER BY block_date", $today)
    );

    $service_labels = array(
        'all' => 'Todo el día',
        'lunch' => 'Comida',
        'dinner' => 'Cena',
    );

    ?>
    <div class="wrap">
        <h1>Bloqueos</h1>

        <?php if (empty($blocks)): ?>
            <p>No hay bloqueos programados.</p>
        <?php else: ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Turno</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blocks as $block): ?>
                        <tr>
                            <td><?php echo esc_html(wp_date('d/m/Y', strtotime($block->block_date), new DateTimeZone('UTC'))); ?>
                            </td>
                            <td><?php echo esc_html($service_labels[$block->service] ?? $block->service); ?></td>
                            <td><?php echo esc_html($block->reason); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>Añadir bloqueo</h2>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="snr_add_block">
            <?php wp_nonce_field('snr_add_block'); ?>

            <p>
                <label for="snr_block_date">Fecha</label>
                <input type="date" id="snr_block_date" name="block_date" min="<?php echo esc_attr($today); ?>" required>
            </p>

            <p>
                <label for="snr_block_service">Turno</label>
                <select id="snr_block_service" name="service">
                    <option value="all">Todo el día</option>
                    <option value="lunch">Comida</option>
                    <option value="dinner">Cena</option>
                </select>
            </p>
            <p>
                <label for="snr_block_reason">Motivo</label>
                <input type="text" id="snr_block_reason" name="reason" maxlength="100"
                    placeholder="Festivo, evento privado…">
            </p>

            <?php submit_button('Añadir bloqueo'); ?>
        </form>
    </div>
    <?php
}

function snr_handle_add_block()
{
    // 1. Permisos: solo administradores
    if (!current_user_can('manage_options')) {
        wp_die('No tienes permisos para hacer esto.');
    }

    // 2. Nonce: 'snr_add_block'
    check_admin_referer('snr_add_block');

    // 3. Recoger y sanear
    $date = sanitize_text_field(wp_unslash($_POST['block_date'] ?? ''));
    $service = sanitize_key($_POST['service'] ?? '');
    $reason = sanitize_text_field(wp_unslash($_POST['reason'] ?? ''));

    // 4. Validar: wp_die( 'Datos no válidos.' ) si se cumple CUALQUIERA de estas:
    if (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
        || $date < current_time('Y-m-d')
        || !in_array($service, array('all', 'lunch', 'dinner'), true)
    ) {
        wp_die('Datos no válidos');
    }

    // 5. Guardar con $wpdb->insert (recuerda global $wpdb)
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'snr_blocks',
        array(
            'block_date' => $date,
            'service' => $service,
            'reason' => $reason,
        ),
        array('%s', '%s', '%s')
    );

    // 6. Volver a la página de bloqueos
    wp_safe_redirect(admin_url('admin.php?page=snr-blocks'));
    exit;
}

// 7. Conectar con el hook: add_action( 'admin_post_snr_add_block', ... );
add_action('admin_post_snr_add_block', 'snr_handle_add_block');