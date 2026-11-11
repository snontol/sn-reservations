<?php

/**
 * Plugin Name: SN Reservations
 * Description: Sistema de reservas para restaurantes con asignación automática de mesas, confirmación por email y panel de gestión.
 * Version: 0.1.0
 * Author: Samuel Nontol
 * Author URI: https://snontol.github.io/nontol.dev/
 * Text Domain: sn-reservations
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

defined('ABSPATH') || exit;

define( 'SNR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SNR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SNR_VERSION', '0.1.0' );

require_once SNR_PLUGIN_DIR . 'includes/activation.php';
require_once SNR_PLUGIN_DIR . 'includes/admin-menu.php';
require_once SNR_PLUGIN_DIR . 'includes/admin-tables.php';
require_once SNR_PLUGIN_DIR . 'includes/availability.php';
require_once SNR_PLUGIN_DIR . 'includes/emails.php';
require_once SNR_PLUGIN_DIR . 'includes/public-form.php';
require_once SNR_PLUGIN_DIR . 'includes/admin-reservations.php';
require_once SNR_PLUGIN_DIR . 'includes/admin-blocks.php';

register_activation_hook(__FILE__, 'snr_activate');
