<?php

class PlandokFrontend {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('plandok_booking_calendar', array($this, 'booking_calendar_shortcode'));
        add_action('wp_ajax_plandok_submit_application', array($this, 'ajax_submit_application'));
        add_action('wp_ajax_nopriv_plandok_submit_application', array($this, 'ajax_submit_application'));
        add_action('wp_ajax_plandok_get_available_slots', array($this, 'ajax_get_available_slots'));
        add_action('wp_ajax_nopriv_plandok_get_available_slots', array($this, 'ajax_get_available_slots'));
    }
    
    public function enqueue_scripts() {
        // Only load on pages with the shortcode or booking page
        if (is_page() && (has_shortcode(get_post()->post_content, 'plandok_booking_calendar') || 
                         get_the_ID() == get_option('plandok_booking_page_id'))) {
            
            wp_enqueue_script('plandok-frontend-js', PLANDOK_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), PLANDOK_VERSION, true);
            wp_enqueue_style('plandok-frontend-css', PLANDOK_PLUGIN_URL . 'assets/css/frontend.css', array(), PLANDOK_VERSION);
            
            wp_localize_script('plandok-frontend-js', 'plandok_frontend', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('plandok_public_nonce'),
                'settings' => get_option('plandok_settings', array()),
                'strings' => array(
                    'loading' => __('Loading...', 'plandok-booking'),
                    'error' => __('An error occurred. Please try again.', 'plandok-booking'),
                    'success' => __('Booking application submitted successfully!', 'plandok-booking'),
                    'select_service' => __('Select a Service', 'plandok-booking'),
                    'click_to_book' => __('Click on an available service slot to view details and book your appointment.', 'plandok-booking'),
                    'no_slots' => __('No available slots for this date.', 'plandok-booking'),
                    'book_appointment' => __('Book Appointment', 'plandok-booking'),
                    'apply_button' => __('Apply for This Appointment', 'plandok-booking'),
                    'required_fields' => __('Please fill in all required fields.', 'plandok-booking')
                )
            ));
        }
    }
    
    public function booking_calendar_shortcode($atts) {
        $atts = shortcode_atts(array(
            'view' => 'calendar',
            'show_staff' => 'true',
            'show_price' => 'true',
            'services' => '', // Comma-separated service IDs to limit display
            'staff' => '', // Comma-separated staff IDs to limit display
            'theme' => 'default'
        ), $atts);
        
        $services = PlandokDatabase::get_services();
        $staff = PlandokDatabase::get_staff();
        
        // Filter services if specified
        if (!empty($atts['services'])) {
            $service_ids = array_map('intval', explode(',', $atts['services']));
            $services = array_filter($services, function($service) use ($service_ids) {
                return in_array($service->id, $service_ids);
            });
        }
        
        // Filter staff if specified
        if (!empty($atts['staff'])) {
            $staff_ids = array_map('intval', explode(',', $atts['staff']));
            $staff = array_filter($staff, function($member) use ($staff_ids) {
                return in_array($member->id, $staff_ids);
            });
        }
        
        ob_start();
        include PLANDOK_PLUGIN_PATH . 'templates/frontend/booking-calendar.php';
        return ob_get_clean();
    }
    
    public function ajax_get_available_slots() {
        check_ajax_referer('plandok_public_nonce', 'nonce');
        
        $date = sanitize_text_field($_POST['date']);
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        
        // Validate date
        if (!$this->is_valid_booking_date($date)) {
            wp_send_json_error(__('Invalid date selected.', 'plandok-booking'));
        }
        
        if ($service_id) {
            // Get slots for specific service
            $slots = PlandokDatabase::get_available_slots($date, $service_id);
        } else {
            // Get all available slots for the date
            $services = PlandokDatabase::get_services();
            $all_slots = array();
            
            foreach ($services as $service) {
                $service_slots = PlandokDatabase::get_available_slots($date, $service->id);
                $all_slots = array_merge($all_slots, $service_slots);
            }
            
            // Sort by time
            usort($all_slots, function($a, $b) {
                return strcmp($a['time'], $b['time']);
            });
            
            $slots = $all_slots;
        }
        
        wp_send_json_success($slots);
    }
    
    public function ajax_submit_application() {
        check_ajax_referer('plandok_public_nonce', 'nonce');
        
        // Validate required fields
        $required_fields = array('service_id', 'first_name', 'last_name', 'email', 'phone', 'preferred_date', 'preferred_time');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(sprintf(__('Field %s is required.', 'plandok-booking'), $field));
            }
        }
        
        // Validate email
        if (!is_email($_POST['email'])) {
            wp_send_json_error(__('Please enter a valid email address.', 'plandok-booking'));
        }
        
        // Validate date
        if (!$this->is_valid_booking_date($_POST['preferred_date'])) {
            wp_send_json_error(__('Invalid date selected.', 'plandok-booking'));
        }
        
        global $wpdb;
        
        $data = array(
            'service_id' => intval($_POST['service_id']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'notes' => sanitize_textarea_field($_POST['notes']),
            'preferred_date' => sanitize_text_field($_POST['preferred_date']),
            'preferred_time' => sanitize_text_field($_POST['preferred_time']),
            'status' => 'pending'
        );
        
        // Check for duplicate applications
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}plandok_applications 
             WHERE email = %s AND service_id = %d AND preferred_date = %s AND preferred_time = %s AND status = 'pending'",
            $data['email'], $data['service_id'], $data['preferred_date'], $data['preferred_time']
        ));
        
        if ($existing) {
            wp_send_json_error(__('You have already submitted an application for this time slot.', 'plandok-booking'));
        }
        
        $table = $wpdb->prefix . 'plandok_applications';
        $result = $wpdb->insert($table, $data);
        
        if ($result) {
            $application_id = $wpdb->insert_id;
            
            // Get full application data for emails
            $application = $wpdb->get_row($wpdb->prepare(
                "SELECT a.*, s.name as service_name, s.duration, s.price
                 FROM $table a
                 LEFT JOIN {$wpdb->prefix}plandok_services s ON a.service_id = s.id
                 WHERE a.id = %d",
                $application_id
            ));
            
            // Send confirmation emails
            PlandokEmail::send_application_confirmation($application);
            PlandokEmail::send_admin_notification($application);
            
            wp_send_json_success(array(
                'message' => __('Your booking application has been submitted successfully! We will contact you soon to confirm your appointment.', 'plandok-booking')
            ));
        } else {
            wp_send_json_error(__('Failed to submit application. Please try again.', 'plandok-booking'));
        }
    }
    
    private function is_valid_booking_date($date) {
        // Check if date is valid format
        $date_obj = DateTime::createFromFormat('Y-m-d', $date);
        if (!$date_obj || $date_obj->format('Y-m-d') !== $date) {
            return false;
        }
        
        // Check if date is not in the past
        if ($date_obj < new DateTime('today')) {
            return false;
        }
        
        // Check booking advance limit
        $settings = get_option('plandok_settings', array());
        $advance_days = isset($settings['booking_advance_days']) ? intval($settings['booking_advance_days']) : 30;
        
        $max_date = new DateTime("+{$advance_days} days");
        if ($date_obj > $max_date) {
            return false;
        }
        
        return true;
    }
}
