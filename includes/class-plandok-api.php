<?php

class PlandokAPI {
    
    public function __construct() {
        add_action('wp_ajax_plandok_create_event', array($this, 'ajax_create_event'));
        add_action('wp_ajax_plandok_create_repeated_events', array($this, 'ajax_create_repeated_events'));
        add_action('wp_ajax_plandok_bulk_delete_future_events', array($this, 'ajax_bulk_delete_future_events'));
    }
    
    public function ajax_create_event() {
        check_ajax_referer('plandok_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'plandok-booking'));
        }
        
        global $wpdb;
        
        $service_id = intval($_POST['service_id']);
        $staff_id = intval($_POST['staff_id']);
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        $full_day = isset($_POST['full_day']) && $_POST['full_day'] === 'true';
        $individual_appointment = isset($_POST['individual_appointment']) && $_POST['individual_appointment'] === 'true';
        
        // Validate inputs
        if (!$service_id || !$staff_id || !$date) {
            wp_send_json_error(__('Missing required fields', 'plandok-booking'));
        }
        
        if (!$full_day && !$time) {
            wp_send_json_error(__('Time is required for single appointments', 'plandok-booking'));
        }
        
        if ($full_day) {
            $events_created = $this->create_full_day_events($service_id, $staff_id, $date);
            wp_send_json_success(array(
                'message' => sprintf(__('%d events created successfully', 'plandok-booking'), $events_created)
            ));
        } else {
            $data = array(
                'service_id' => $service_id,
                'staff_id' => $staff_id,
                'appointment_date' => $date,
                'appointment_time' => $time . ':00',
                'status' => 'available',
                'created_by' => get_current_user_id()
            );
            
            $result = $wpdb->insert($wpdb->prefix . 'plandok_appointments', $data);
            
            if ($result) {
                wp_send_json_success(array('message' => __('Event created successfully', 'plandok-booking')));
            } else {
                wp_send_json_error(__('Failed to create event', 'plandok-booking'));
            }
        }
    }
    
    public function ajax_create_repeated_events() {
        check_ajax_referer('plandok_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'plandok-booking'));
        }
        
        global $wpdb;
        
        $service_id = intval($_POST['service_id']);
        $staff_id = intval($_POST['staff_id']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $time = sanitize_text_field($_POST['time']);
        $repeat_count = intval($_POST['repeat_count']);
        $repeat_unit = sanitize_text_field($_POST['repeat_unit']); // 'days' or 'weeks'
        $full_day = isset($_POST['full_day']) && $_POST['full_day'] === 'true';
        
        // Validate inputs
        if (!$service_id || !$staff_id || !$start_date || !$repeat_count) {
            wp_send_json_error(__('Missing required fields', 'plandok-booking'));
        }
        
        if ($repeat_count > 52) {
            wp_send_json_error(__('Maximum 52 repetitions allowed', 'plandok-booking'));
        }
        
        $total_events = 0;
        
        for ($i = 0; $i < $repeat_count; $i++) {
            $days_to_add = $repeat_unit === 'days' ? $i : $i * 7;
            $current_date = date('Y-m-d', strtotime($start_date . ' +' . $days_to_add . ' days'));
            
            if ($full_day) {
                $events_created = $this->create_full_day_events($service_id, $staff_id, $current_date);
                $total_events += $events_created;
            } else {
                $data = array(
                    'service_id' => $service_id,
                    'staff_id' => $staff_id,
                    'appointment_date' => $current_date,
                    'appointment_time' => $time . ':00',
                    'status' => 'available',
                    'created_by' => get_current_user_id()
                );
                
                $result = $wpdb->insert($wpdb->prefix . 'plandok_appointments', $data);
                if ($result) {
                    $total_events++;
                }
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('%d events created successfully', 'plandok-booking'), $total_events)
        ));
    }
    
    private function create_full_day_events($service_id, $staff_id, $date) {
        global $wpdb;
        
        // Get service duration
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT duration FROM {$wpdb->prefix}plandok_services WHERE id = %d",
            $service_id
        ));
        
        if (!$service) {
            return 0;
        }
        
        $duration_minutes = $this->parse_duration_to_minutes($service->duration);
        
        // Get working hours from settings
        $settings = get_option('plandok_settings', array());
        $start_hour = 9;
        $end_hour = 18;
        
        if (!empty($settings['working_hours_start'])) {
            $start_hour = intval(substr($settings['working_hours_start'], 0, 2));
        }
        
        if (!empty($settings['working_hours_end'])) {
            $end_hour = intval(substr($settings['working_hours_end'], 0, 2));
        }
        
        $working_minutes = ($end_hour - $start_hour) * 60;
        $appointments_per_day = floor($working_minutes / $duration_minutes);
        
        $events_created = 0;
        
        for ($i = 0; $i < $appointments_per_day; $i++) {
            $start_minutes = $start_hour * 60 + ($i * $duration_minutes);
            $hour = floor($start_minutes / 60);
            $minute = $start_minutes % 60;
            
            if ($hour >= $end_hour) break;
            
            $time = sprintf('%02d:%02d:00', $hour, $minute);
            
            // Check if this time slot already exists
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}plandok_appointments 
                 WHERE service_id = %d AND staff_id = %d AND appointment_date = %s AND appointment_time = %s",
                $service_id, $staff_id, $date, $time
            ));
            
            if (!$existing) {
                $data = array(
                    'service_id' => $service_id,
                    'staff_id' => $staff_id,
                    'appointment_date' => $date,
                    'appointment_time' => $time,
                    'status' => 'available',
                    'created_by' => get_current_user_id()
                );
                
                $result = $wpdb->insert($wpdb->prefix . 'plandok_appointments', $data);
                if ($result) {
                    $events_created++;
                }
            }
        }
        
        return $events_created;
    }
    
    private function parse_duration_to_minutes($duration) {
        $total_minutes = 0;
        
        // Parse hours
        if (preg_match('/(\d+)\s*h/', $duration, $matches)) {
            $total_minutes += intval($matches[1]) * 60;
        }
        
        // Parse minutes
        if (preg_match('/(\d+)\s*min/', $duration, $matches)) {
            $total_minutes += intval($matches[1]);
        }
        
        return $total_minutes ?: 30; // Default to 30 minutes
    }
    
    public function ajax_bulk_delete_future_events() {
        check_ajax_referer('plandok_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'plandok-booking'));
        }
        
        global $wpdb;
        
        $today = date('Y-m-d');
        
        // Only delete available appointments (not booked ones)
        $deleted = $wpdb->delete(
            $wpdb->prefix . 'plandok_appointments',
            array(
                'appointment_date >' => $today,
                'status' => 'available'
            ),
            array('%s', '%s')
        );
        
        wp_send_json_success(array(
            'message' => sprintf(__('Deleted %d future available appointments', 'plandok-booking'), $deleted)
        ));
    }
}
