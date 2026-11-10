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