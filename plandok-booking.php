<?php
/**
 * Plugin Name: Plandok Booking System
 * Plugin URI: https://github.com/plandok/plandok-booking-wordpress
 * Description: Complete appointment booking system for WordPress with calendar management, services, staff, and customer applications.
 * Version: 1.0.0
 * Author: Plandok
 * Author URI: https://plandok.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: plandok-booking
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PLANDOK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PLANDOK_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PLANDOK_VERSION', '1.0.0');
define('PLANDOK_DB_VERSION', '1.0.0');

// Main plugin class
class PlandokBooking {

    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('PlandokBooking', 'uninstall'));
    }

    public function init() {
        // Check minimum requirements
        if (!$this->check_requirements()) {
            return;
        }

        // Load plugin classes
        $this->load_dependencies();

        // Initialize admin and frontend
        if (is_admin()) {
            new PlandokAdmin();
        }
        new PlandokFrontend();
        new PlandokAPI();

        // Load textdomain
        load_plugin_textdomain('plandok-booking', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    private function check_requirements() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' .
                     __('Plandok Booking requires PHP 7.4 or higher.', 'plandok-booking') .
                     '</p></div>';
            });
            return false;
        }

        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.0', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' .
                     __('Plandok Booking requires WordPress 5.0 or higher.', 'plandok-booking') .
                     '</p></div>';
            });
            return false;
        }

        return true;
    }

    private function load_dependencies() {
        require_once PLANDOK_PLUGIN_PATH . 'includes/class-plandok-database.php';
        require_once PLANDOK_PLUGIN_PATH . 'includes/class-plandok-admin.php';
        require_once PLANDOK_PLUGIN_PATH . 'includes/class-plandok-frontend.php';
        require_once PLANDOK_PLUGIN_PATH . 'includes/class-plandok-api.php';
        require_once PLANDOK_PLUGIN_PATH . 'includes/class-plandok-email.php';
        require_once PLANDOK_PLUGIN_PATH . 'includes/class-plandok-settings.php';
    }

    public function activate() {
        // Load dependencies first
        $this->load_dependencies();

        // Create database tables
        PlandokDatabase::create_tables();

        // Create default pages
        $this->create_default_pages();

        // Set default options
        $this->set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Add activation notice
        add_option('plandok_activation_notice', true);
    }

    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear scheduled events
        wp_clear_scheduled_hook('plandok_daily_cleanup');
    }

    public static function uninstall() {
        // Remove all plugin data if user chooses to
        if (get_option('plandok_remove_data_on_uninstall', false)) {
            global $wpdb;

            // Drop custom tables
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}plandok_services");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}plandok_staff");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}plandok_appointments");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}plandok_applications");

            // Remove options
            delete_option('plandok_version');
            delete_option('plandok_db_version');
            delete_option('plandok_settings');
            delete_option('plandok_remove_data_on_uninstall');

            // Remove pages
            $booking_page = get_page_by_path('book-appointment');
            if ($booking_page) {
                wp_delete_post($booking_page->ID, true);
            }
        }
    }

    private function create_default_pages() {
        // Create booking page
        $booking_page = array(
            'post_title'    => __('Book Appointment', 'plandok-booking'),
            'post_content'  => '[plandok_booking_calendar]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_slug'     => 'book-appointment',
            'meta_input'    => array(
                '_plandok_page' => 'booking'
            )
        );

        if (!get_page_by_path('book-appointment')) {
            $page_id = wp_insert_post($booking_page);
            update_option('plandok_booking_page_id', $page_id);
        }
    }

    private function set_default_options() {
        // Set plugin version
        update_option('plandok_version', PLANDOK_VERSION);
        update_option('plandok_db_version', PLANDOK_DB_VERSION);

        // Set default settings
        $default_settings = array(
            'working_hours_start' => '09:00',
            'working_hours_end' => '18:00',
            'time_slot_duration' => 15,
            'booking_advance_days' => 30,
            'admin_email_notifications' => true,
            'customer_email_notifications' => true,
            'require_approval' => true
        );

        add_option('plandok_settings', $default_settings);

        // Schedule daily cleanup
        if (!wp_next_scheduled('plandok_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'plandok_daily_cleanup');
        }
    }
}

// Initialize the plugin
new PlandokBooking();
