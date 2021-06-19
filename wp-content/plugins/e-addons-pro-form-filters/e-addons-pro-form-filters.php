<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       e-addons PRO FORM FILTERS
 * Plugin URI:        https://e-addons.com
 * Description:       The unique Posts Search filter based on Elementor
 * Version:           1.2.2
 * Author:            Nerds Farm
 * Author URI:        https://nerds.farm
 * Text Domain:       e-addons-pro-form-filters
 * Domain Path:       /languages
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package e-addons
 * @category Pro Form Filters
 *
 */
// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Load Elements
 *
 * Load the plugin after Elementor (and other plugins) are loaded.
 *
 * @since 0.1.0
 */
add_action('e_addons/loaded', function() {
    if (defined('ELEMENTOR_PRO__FILE__')) {
        require_once( __DIR__ . DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'plugin.php' );
        $plugin = new \EAddonsProFormFilters\Plugin();
    }
});
add_action('plugins_loaded', function() {
    if (!function_exists('e_addons_load_plugin') || !defined('ELEMENTOR_PRO__FILE__')) {
        add_action('admin_notices', function() {
            $message = __('You need to activate "Elementor Free", "Elementor PRO" and "e-addons Free" in order to use "e-addons PRO FORM FILTERS" plugin.', 'elementor');
            echo '<div class="notice notice-error"><p>' . $message . '</p></div>';
        });
    }
}, 11);
