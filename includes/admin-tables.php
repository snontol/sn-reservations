<?php

defined( 'ABSPATH' ) || exit;


function snr_render_tables_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'snr_tables';
    $tables     = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id");

    echo '<div class="wrap"><h1>Mesas</h1>';
    echo '<table class="widefat striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Nombre</th>';
    echo '<th>Capacidad</th>';
    echo '<th>Activa</th>';
    echo '<th>Acciones</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($tables as $table) {
        echo '<tr>';
        echo '<td>' . esc_html($table->name) . '</td>';
        echo '<td>' . esc_html($table->capacity) . '</td>';
        echo '<td>' . ($table->is_active ? 'Sí' : 'No') . '</td>';

?>
        <td>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="snr_toggle_table">
                <input type="hidden" name="table_id" value="<?php echo esc_attr($table->id); ?>">
                <input type="hidden" name="new_status" value="<?php echo $table->is_active ? 0 : 1; ?>">
                <?php wp_nonce_field('snr_toggle_table'); ?>
                <button type="submit" class="button">
                    <?php echo $table->is_active ? 'Desactivar' : 'Activar'; ?>
                </button>
            </form>
        </td>
    <?php

        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    ?>
    <h2>Añadir mesa</h2>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="snr_add_table">
        <?php wp_nonce_field('snr_add_table'); ?>

        <p>
            <label for="snr_table_name">Nombre</label>
            <input type="text" id="snr_table_name" name="table_name" maxlength="50" required>
        </p>
        <p>
            <label for="snr_table_capacity">Capacidad</label>
            <input type="number" id="snr_table_capacity" name="table_capacity" min="1" max="20" required>
        </p>

        <?php submit_button('Añadir mesa'); ?>
    </form>
<?php

    echo '</div>';
}

function snr_handle_add_table()
{
    // 1. Permisos: solo administradores
    if (! current_user_can('manage_options')) {
        wp_die('No tienes permisos para hacer esto.');
    }

    // 2. Nonce: el formulario viene de tu panel
    check_admin_referer('snr_add_table');

    // 3. Recoger y sanear los datos
    $name     = sanitize_text_field(wp_unslash($_POST['table_name'] ?? ''));
    $capacity = absint($_POST['table_capacity'] ?? 0);

    // 4. Validar 
    if (($name === '') || ($capacity < 1) || ($capacity > 20)) {
        wp_die('Datos no válidos');
    }

    // 5. Guardar en la base de datos
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'snr_tables',
        array(
            'name'     => $name,
            'capacity' => $capacity,
        ),
        array('%s', '%d')
    );

    // 6. Volver a la página de mesas
    wp_safe_redirect(admin_url('admin.php?page=snr-tables'));
    exit;
}

add_action('admin_post_snr_add_table', 'snr_handle_add_table');

function snr_handle_toggle_table()
{
    // 1. Permisos: solo administradores
    if (! current_user_can('manage_options')) {
        wp_die('No tienes permisos para hacer esto.');
    }

    // 2. Nonce: el formulario viene de tu panel
    check_admin_referer('snr_toggle_table');

    // 3. Recoger y sanear los datos
    $table_id   = absint($_POST['table_id'] ?? 0);
    $new_status = absint($_POST['new_status'] ?? 0);

    // 4. Validar 
    if ($table_id < 1 || $new_status > 1) {
        wp_die('Datos no válidos');
    }

    // 5. Actualizar en la base de datos
    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'snr_tables',     // tabla
        array('is_active' => $new_status), // qué cambiar
        array('id' => $table_id),             // en qué fila (el WHERE)
        array('%d'),                    // formato de lo que cambias
        array('%d')                     // formato del WHERE
    );

    // 6. Volver a la página de mesas
    wp_safe_redirect(admin_url('admin.php?page=snr-tables'));
    exit;
}

add_action('admin_post_snr_toggle_table', 'snr_handle_toggle_table');

