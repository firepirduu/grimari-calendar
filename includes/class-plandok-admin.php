<?php

class PlandokAdmin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_notices', array($this, 'admin_notices'));
        
        // AJAX handlers
        add_action('wp_ajax_plandok_save_service', array($this, 'ajax_save_service'));
        add_action('wp_ajax_plandok_delete_service', array($this, 'ajax_delete_service'));
        add_action('wp_ajax_plandok_save_staff', array($this, 'ajax_save_staff'));
        add_action('wp_ajax_plandok_delete_staff', array($this, 'ajax_delete_staff'));
        add_action('wp_ajax_plandok_save_appointment', array($this, 'ajax_save_appointment'));
        add_action('wp_ajax_plandok_delete_appointment', array($this, 'ajax_delete_appointment'));
        add_action('wp_ajax_plandok_update_application_status', array($this, 'ajax_update_application_status'));
        add_action('wp_ajax_plandok_get_calendar_events', array($this, 'ajax_get_calendar_events'));
    }
    
    public function admin_notices() {
        // Show activation notice
        if (get_option('plandok_activation_notice')) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>' . __('Plandok Booking System activated!', 'plandok-booking') . '</strong> ';
            echo sprintf(
                __('Visit the <a href="%s">dashboard</a> to get started or view the <a href="%s" target="_blank">booking page</a>.', 'plandok-booking'),
                admin_url('admin.php?page=plandok-dashboard'),
                get_permalink(get_option('plandok_booking_page_id'))
            );
            echo '</p>';
            echo '</div>';
            delete_option('plandok_activation_notice');
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Plandok Booking', 'plandok-booking'),
            __('Plandok', 'plandok-booking'),
            'manage_options',
            'plandok-dashboard',
            array($this, 'dashboard_page'),
            'dashicons-calendar-alt',
            30
        );
        
        add_submenu_page(
            'plandok-dashboard',
            __('Dashboard', 'plandok-booking'),
            __('Dashboard', 'plandok-booking'),
            'manage_options',
            'plandok-dashboard',
            array($this, 'dashboard_page')
        );
        
        add_submenu_page(
            'plandok-dashboard',
            __('Calendar', 'plandok-booking'),
            __('Calendar', 'plandok-booking'),
            'manage_options',
            'plandok-calendar',
            array($this, 'calendar_page')
        );
        
        add_submenu_page(
            'plandok-dashboard',
            __('Applications', 'plandok-booking'),
            __('Applications', 'plandok-booking'),
            'manage_options',
            'plandok-applications',
            array($this, 'applications_page')
        );
        
        add_submenu_page(
            'plandok-dashboard',
            __('Services', 'plandok-booking'),
            __('Services', 'plandok-booking'),
            'manage_options',
            'plandok-services',
            array($this, 'services_page')
        );
        
        add_submenu_page(
            'plandok-dashboard',
            __('Staff', 'plandok-booking'),
            __('Staff', 'plandok-booking'),
            'manage_options',
            'plandok-staff',
            array($this, 'staff_page')
        );
        
        add_submenu_page(
            'plandok-dashboard',
            __('Settings', 'plandok-booking'),
            __('Settings', 'plandok-booking'),
            'manage_options',
            'plandok-settings',
            array($this, 'settings_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'plandok') === false) {
            return;
        }
        
        wp_enqueue_script('plandok-admin-js', PLANDOK_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), PLANDOK_VERSION, true);
        wp_enqueue_style('plandok-admin-css', PLANDOK_PLUGIN_URL . 'assets/css/admin.css', array(), PLANDOK_VERSION);
        
        // Enqueue WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_localize_script('plandok-admin-js', 'plandok_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('plandok_nonce'),
            'plugin_url' => PLANDOK_PLUGIN_URL,
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this item?', 'plandok-booking'),
                'loading' => __('Loading...', 'plandok-booking'),
                'error' => __('An error occurred. Please try again.', 'plandok-booking'),
                'success' => __('Operation completed successfully.', 'plandok-booking')
            )
        ));
    }
    
    public function dashboard_page() {
        $upcoming_appointments = PlandokDatabase::get_appointments(date('Y-m-d'));
        $total_services = count(PlandokDatabase::get_services());
        $total_staff = count(PlandokDatabase::get_staff());
        $pending_applications = count(PlandokDatabase::get_applications('pending'));
        
        include PLANDOK_PLUGIN_PATH . 'templates/admin/dashboard.php';
    }
    
    public function calendar_page() {
        $services = PlandokDatabase::get_services();
        $staff = PlandokDatabase::get_staff();
        $appointments = PlandokDatabase::get_appointments();
        
        include PLANDOK_PLUGIN_PATH . 'templates/admin/calendar.php';
    }
    
    public function applications_page() {
        $applications = PlandokDatabase::get_applications();
        $services = PlandokDatabase::get_services();
        
        include PLANDOK_PLUGIN_PATH . 'templates/admin/applications.php';
    }
    
    public function services_page() {
        $services = PlandokDatabase::get_services();
        $staff = PlandokDatabase::get_staff();
        
        include PLANDOK_PLUGIN_PATH . 'templates/admin/services.php';
    }
    
    public function staff_page() {
        $staff = PlandokDatabase::get_staff();
        
        include PLANDOK_PLUGIN_PATH . 'templates/admin/staff.php';
    }
    
    public function settings_page() {
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['plandok_settings_nonce'], 'plandok_settings')) {
            $settings = array(
                'working_hours_start' => sanitize_text_field($_POST['working_hours_start']),
                'working_hours_end' => sanitize_text_field($_POST['working_hours_end']),
                'time_slot_duration' => intval($_POST['time_slot_duration']),
                'booking_advance_days' => intval($_POST['booking_advance_days']),
                'admin_email_notifications' => isset($_POST['admin_email_notifications']),
                'customer_email_notifications' => isset($_POST['customer_email_notifications']),
                'require_approval' => isset($_POST['require_approval']),
                'remove_data_on_uninstall' => isset($_POST['remove_data_on_uninstall'])
            );
            
            update_option('plandok_settings', $settings);
            update_option('plandok_remove_data_on_uninstall', $settings['remove_data_on_uninstall']);
            
            echo '<div class="notice notice-success"><p>' . __('Settings saved.', 'plandok-booking') . '</p></div>';
        }
        
        $settings = get_option('plandok_settings', array());
        include PLANDOK_PLUGIN_PATH . 'templates/admin/settings.php';
    }
    
    // AJAX handlers
    public function ajax_save_service() {
        check_ajax_referer('plandok_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'plandok-booking'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'plandok_services';
        
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'category' => sanitize_text_field($_POST['category']),
            'duration' => sanitize_text_field($_POST['duration']),
            'price' => floatval($_POST['price']),
            'extra_time_before' => sanitize_text_field($_POST['extra_time_before']),
            'extra_time_after' => sanitize_text_field($_POST['extra_time_after']),
            'available_online' => intval($_POST['available_online']),
            'description' => sanitize_textarea_field($_POST['description']),
            'staff_ids' => sanitize_text_field($_POST['staff_ids'])
        );
        
        if (isset($_POST['id']) && $_POST['id']) {
            $result = $wpdb->update($table, $data, array('id' => intval($_POST['id'])));
            $message = __('Service updated successfully', 'plandok-booking');
        } else {
            $result = $wpdb->insert($table, $data);
            $message = __('Service created successfully', 'plandok-booking');
        }
        
        if ($result !== false) {
            wp_send_json_success(array('message' => $message));
        } else {
            wp_send_json_error(__('Failed to save service', 'plandok-booking'));
        }
    }
    
    public function ajax_delete_service() {
        check_ajax_referer('plandok_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'plandok-booking'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'plandok_services';
        
        $id = intval($_POST['id']);
        $result = $wpdb->delete($table, array('id' => $id));
        
        if ($result) {
            wp_send_json_success(array('message' => __('Service deleted successfully', 'plandok-booking')));
        } else {
            wp_send_json_error(__('Failed to delete service', 'plandok-booking'));
        }
    }
    
    public function ajax_save_staff() {
        check_ajax_referer('plandok_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'plandok-booking'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'plandok_staff';
        
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'email' => sanitize_email($_POST['email']),
            'position' => sanitize_text_field($_POST['position']),
            'phone' => sanitize_text_field($_POST['phone']),
            'working_hours' => sanitize_textarea_field($_POST['working_hours'])
        );
        
        if (isset($_POST['id']) && $_POST['id']) {
            $result = $wpdb->update($table, $data, array('id' => intval($_POST['id'])));
            $message = __('Staff member updated successfully', 'plandok-booking');
        } else {
            $result = $wpdb->insert($table, $data);
            $message = __('Staff member created successfully', 'plandok-booking');
        }
        
        if ($result !== false) {
            wp_send_json_success(array('message' => $message));
        } else {
            wp_send_json_error(__('Failed to save staff member', 'plandok-booking'));
        }
    }
    
    public function ajax_delete_staff() {
        check_ajax_referer('plandok_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'plandok-booking'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'plandok_staff';
        
        $id = intval($_POST['id']);
        $result = $wpdb->delete($table, array('id' => $id));
        
        if ($result) {
            wp_send_json_success(array('message' => __('Staff member deleted successfully', 'plandok-booking')));
        } else {
            wp_send_json_error(__('Failed to delete staff member', 'plandok-booking'));
        }
    }
    
    public function ajax_save_appointment() {
        check_ajax_referer('plandok_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'plandok-booking'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'plandok_appointments';
        
        $data = array(
            'service_id' => intval($_POST['service_id']),
            'staff_id' => intval($_POST['staff_id']),
            'appointment_date' => sanitize_text_field($_POST['appointment_date']),
            'appointment_time' => sanitize_text_field($_POST['appointment_time']),
            'client_name' => sanitize_text_field($_POST['client_name']),
            'client_email' => sanitize_email($_POST['client_email']),
            'client_phone' => sanitize_text_field($_POST['client_phone']),
            'status' => sanitize_text_field($_POST['status']),
            'notes' => sanitize_textarea_field($_POST['notes']),
            'location' => sanitize_text_field($_POST['location']),
            'created_by' => get_current_user_id()
        );
        
        if (isset($_POST['id']) && $_POST['id']) {
            unset($data['created_by']); // Don't update creator on edit
            $result = $wpdb->update($table, $data, array('id' => intval($_POST['id'])));
            $message = __('Appointment updated successfully', 'plandok-booking');
        } else {
            $result = $wpdb->insert($table, $data);
            $message = __('Appointment created successfully', 'plandok-booking');
        }
        
        if ($result !== false) {
            wp_send_json_success(array('message' => $message));
        } else {
            wp_send_json_error(__('Failed to save appointment', 'plandok-booking'));
        }
    }
    
    public function ajax_delete_appointment() {
        check_ajax_referer('plandok_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'plandok-booking'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'plandok_appointments';
        
        $id = intval($_POST['id']);
        $result = $wpdb->delete($table, array('id' => $id));
        
        if ($result) {
            wp_send_json_success(array('message' => __('Appointment deleted successfully', 'plandok-booking')));
        } else {
            wp_send_json_error(__('Failed to delete appointment', 'plandok-booking'));
        }
    }
    
    public function ajax_update_application_status() {
        check_ajax_referer('plandok_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'plandok-booking'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'plandok_applications';
        
        $id = intval($_POST['id']);
        $status = sanitize_text_field($_POST['status']);
        $rejection_reason = isset($_POST['rejection_reason']) ? sanitize_textarea_field($_POST['rejection_reason']) : '';
        
        $data = array(
            'status' => $status,
            'processed_at' => current_time('mysql'),
            'processed_by' => get_current_user_id()
        );
        
        if ($status === 'rejected' && $rejection_reason) {
            $data['rejection_reason'] = $rejection_reason;
        }
        
        $result = $wpdb->update($table, $data, array('id' => $id));
        
        if ($result !== false) {
            // Send notification email
            $application = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d", $id
            ));
            
            if ($application) {
                PlandokEmail::send_application_status_update($application, $status);
            }
            
            wp_send_json_success(array('message' => __('Application status updated successfully', 'plandok-booking')));
        } else {
            wp_send_json_error(__('Failed to update application status', 'plandok-booking'));
        }
    }
    
    public function ajax_get_calendar_events() {
        check_ajax_referer('plandok_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'plandok-booking'));
        }
        
        $month = intval($_POST['month']);
        $year = intval($_POST['year']);
        
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = date('Y-m-t', strtotime($start_date));
        
        global $wpdb;
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, s.name as service_name, s.category, s.duration, s.price, 
                    st.name as staff_name
             FROM {$wpdb->prefix}plandok_appointments a
             LEFT JOIN {$wpdb->prefix}plandok_services s ON a.service_id = s.id
             LEFT JOIN {$wpdb->prefix}plandok_staff st ON a.staff_id = st.id
             WHERE a.appointment_date BETWEEN %s AND %s
             ORDER BY a.appointment_date, a.appointment_time",
            $start_date, $end_date
        ));
        
        $events = array();
        foreach ($appointments as $appointment) {
            $events[] = array(
                'id' => $appointment->id,
                'title' => $appointment->service_name,
                'date' => $appointment->appointment_date,
                'time' => substr($appointment->appointment_time, 0, 5),
                'duration' => $appointment->duration,
                'price' => $appointment->price,
                'client_name' => $appointment->client_name ?: __('Available', 'plandok-booking'),
                'client_email' => $appointment->client_email,
                'client_phone' => $appointment->client_phone,
                'staff_name' => $appointment->staff_name,
                'location' => $appointment->location,
                'status' => $appointment->status,
                'notes' => $appointment->notes,
                'category' => $appointment->category
            );
        }
        
        wp_send_json_success($events);
    }
}
