<?php

class PlandokDatabase {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Services table
        $table_services = $wpdb->prefix . 'plandok_services';
        $sql_services = "CREATE TABLE $table_services (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            category varchar(100) NOT NULL,
            duration varchar(20) NOT NULL,
            price decimal(10,2) NOT NULL,
            extra_time_before varchar(20) DEFAULT '0min',
            extra_time_after varchar(20) DEFAULT '0min',
            available_online tinyint(1) DEFAULT 1,
            description text,
            staff_ids text,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY category (category)
        ) $charset_collate;";
        
        // Staff table
        $table_staff = $wpdb->prefix . 'plandok_staff';
        $sql_staff = "CREATE TABLE $table_staff (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            position varchar(100) NOT NULL,
            phone varchar(20),
            working_hours text,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY status (status)
        ) $charset_collate;";
        
        // Appointments table
        $table_appointments = $wpdb->prefix . 'plandok_appointments';
        $sql_appointments = "CREATE TABLE $table_appointments (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            service_id mediumint(9) NOT NULL,
            staff_id mediumint(9) NOT NULL,
            appointment_date date NOT NULL,
            appointment_time time NOT NULL,
            client_name varchar(255),
            client_email varchar(255),
            client_phone varchar(20),
            status varchar(20) DEFAULT 'available',
            notes text,
            location varchar(255) DEFAULT 'Main Location',
            created_by int DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY service_id (service_id),
            KEY staff_id (staff_id),
            KEY appointment_date (appointment_date),
            KEY status (status),
            KEY appointment_datetime (appointment_date, appointment_time)
        ) $charset_collate;";
        
        // Applications table
        $table_applications = $wpdb->prefix . 'plandok_applications';
        $sql_applications = "CREATE TABLE $table_applications (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            service_id mediumint(9) NOT NULL,
            appointment_id mediumint(9),
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20) NOT NULL,
            notes text,
            status varchar(20) DEFAULT 'pending',
            preferred_date date,
            preferred_time time,
            rejection_reason text,
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            processed_at datetime,
            processed_by int,
            PRIMARY KEY (id),
            KEY service_id (service_id),
            KEY appointment_id (appointment_id),
            KEY status (status),
            KEY submitted_at (submitted_at),
            KEY email (email)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_services);
        dbDelta($sql_staff);
        dbDelta($sql_appointments);
        dbDelta($sql_applications);
        
        // Insert default data
        self::insert_default_data();
        
        // Update database version
        update_option('plandok_db_version', PLANDOK_DB_VERSION);
    }
    
    private static function insert_default_data() {
        global $wpdb;
        
        // Check if data already exists
        $existing_services = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}plandok_services");
        if ($existing_services > 0) {
            return;
        }
        
        // Insert default services
        $default_services = array(
            array(
                'name' => "Man's Haircut",
                'category' => 'Hair',
                'duration' => '30min',
                'price' => 10.00,
                'description' => 'Quick and professional haircut for men',
                'staff_ids' => '1'
            ),
            array(
                'name' => "Women's Cut & Style",
                'category' => 'Hair',
                'duration' => '1h 30min',
                'price' => 45.00,
                'description' => 'Professional women\'s haircut and styling',
                'staff_ids' => '1,2'
            ),
            array(
                'name' => 'Hair Coloring',
                'category' => 'Hair',
                'duration' => '2h',
                'price' => 80.00,
                'description' => 'Full hair coloring service with consultation',
                'staff_ids' => '2'
            )
        );
        
        $table_services = $wpdb->prefix . 'plandok_services';
        foreach ($default_services as $service) {
            $wpdb->insert($table_services, $service);
        }
        
        // Insert default staff
        $default_staff = array(
            array(
                'name' => 'Haralds Gribusts',
                'email' => 'haralds@plandok.com',
                'position' => 'Hair Stylist',
                'phone' => '+1234567890',
                'working_hours' => 'Monday-Friday: 9:00 AM - 6:00 PM, Saturday: 10:00 AM - 4:00 PM'
            ),
            array(
                'name' => 'Sarah Johnson',
                'email' => 'sarah@plandok.com',
                'position' => 'Hair Stylist',
                'phone' => '+1234567891',
                'working_hours' => 'Tuesday-Saturday: 10:00 AM - 7:00 PM'
            )
        );
        
        $table_staff = $wpdb->prefix . 'plandok_staff';
        foreach ($default_staff as $staff) {
            $wpdb->insert($table_staff, $staff);
        }
        
        // Insert sample appointments
        $sample_appointments = array(
            array(
                'service_id' => 1,
                'staff_id' => 1,
                'appointment_date' => date('Y-m-d'),
                'appointment_time' => '09:00:00',
                'client_name' => 'John Smith',
                'client_email' => 'john.smith@email.com',
                'client_phone' => '+1234567890',
                'status' => 'booked'
            ),
            array(
                'service_id' => 2,
                'staff_id' => 2,
                'appointment_date' => date('Y-m-d', strtotime('+1 day')),
                'appointment_time' => '14:30:00',
                'client_name' => 'Emma Wilson',
                'client_email' => 'emma.wilson@email.com',
                'client_phone' => '+1234567891',
                'status' => 'booked'
            )
        );
        
        $table_appointments = $wpdb->prefix . 'plandok_appointments';
        foreach ($sample_appointments as $appointment) {
            $wpdb->insert($table_appointments, $appointment);
        }
    }
    
    public static function get_services($status = 'active') {
        global $wpdb;
        $table = $wpdb->prefix . 'plandok_services';
        
        if ($status) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE status = %s ORDER BY name",
                $status
            ));
        }
        
        return $wpdb->get_results("SELECT * FROM $table ORDER BY name");
    }
    
    public static function get_staff($status = 'active') {
        global $wpdb;
        $table = $wpdb->prefix . 'plandok_staff';
        
        if ($status) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE status = %s ORDER BY name",
                $status
            ));
        }
        
        return $wpdb->get_results("SELECT * FROM $table ORDER BY name");
    }
    
    public static function get_appointments($date = null, $staff_id = null, $status = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'plandok_appointments';
        
        $where = "WHERE 1=1";
        $params = array();
        
        if ($date) {
            $where .= " AND appointment_date = %s";
            $params[] = $date;
        }
        
        if ($staff_id) {
            $where .= " AND staff_id = %d";
            $params[] = $staff_id;
        }
        
        if ($status) {
            $where .= " AND a.status = %s";
            $params[] = $status;
        }
        
        $sql = "SELECT a.*, s.name as service_name, s.category, s.duration, s.price, 
                st.name as staff_name
                FROM $table a
                LEFT JOIN {$wpdb->prefix}plandok_services s ON a.service_id = s.id
                LEFT JOIN {$wpdb->prefix}plandok_staff st ON a.staff_id = st.id
                $where
                ORDER BY appointment_date, appointment_time";
        
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }
        
        return $wpdb->get_results($sql);
    }
    
    public static function get_applications($status = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'plandok_applications';
        
        $where = "WHERE 1=1";
        $params = array();
        
        if ($status) {
            $where .= " AND status = %s";
            $params[] = $status;
        }
        
        $sql = "SELECT a.*, s.name as service_name, s.duration, s.price
                FROM $table a
                LEFT JOIN {$wpdb->prefix}plandok_services s ON a.service_id = s.id
                $where
                ORDER BY submitted_at DESC";
        
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }
        
        return $wpdb->get_results($sql);
    }
    
    public static function get_available_slots($date, $service_id) {
        global $wpdb;
        
        // Get service details
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}plandok_services WHERE id = %d AND status = 'active'", 
            $service_id
        ));
        
        if (!$service) {
            return array();
        }
        
        // Get available staff for this service
        $staff_ids = !empty($service->staff_ids) ? explode(',', $service->staff_ids) : array();
        if (empty($staff_ids)) {
            return array();
        }
        
        // Get booked appointments for the date
        $booked_slots = $wpdb->get_results($wpdb->prepare(
            "SELECT appointment_time, staff_id FROM {$wpdb->prefix}plandok_appointments 
             WHERE appointment_date = %s AND service_id = %d AND status != 'cancelled'",
            $date, $service_id
        ));
        
        $booked_by_staff = array();
        foreach ($booked_slots as $slot) {
            $booked_by_staff[$slot->staff_id][] = $slot->appointment_time;
        }
        
        // Generate available time slots
        $settings = get_option('plandok_settings', array());
        $start_hour = 9;
        $end_hour = 18;
        $interval = 15;
        
        if (!empty($settings['working_hours_start'])) {
            $start_hour = intval(substr($settings['working_hours_start'], 0, 2));
        }
        
        if (!empty($settings['working_hours_end'])) {
            $end_hour = intval(substr($settings['working_hours_end'], 0, 2));
        }
        
        if (!empty($settings['time_slot_duration'])) {
            $interval = intval($settings['time_slot_duration']);
        }
        
        $available_slots = array();
        
        for ($hour = $start_hour; $hour < $end_hour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += $interval) {
                $time = sprintf('%02d:%02d:00', $hour, $minute);
                
                // Check if any staff member is available for this time
                $available_staff = array();
                foreach ($staff_ids as $staff_id) {
                    $staff_id = intval(trim($staff_id));
                    if (!isset($booked_by_staff[$staff_id]) || 
                        !in_array($time, $booked_by_staff[$staff_id])) {
                        
                        $staff = $wpdb->get_row($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}plandok_staff WHERE id = %d AND status = 'active'",
                            $staff_id
                        ));
                        
                        if ($staff) {
                            $available_staff[] = $staff;
                        }
                    }
                }
                
                if (!empty($available_staff)) {
                    $available_slots[] = array(
                        'time' => substr($time, 0, 5),
                        'service_name' => $service->name,
                        'service_id' => $service->id,
                        'duration' => $service->duration,
                        'price' => $service->price,
                        'category' => $service->category,
                        'available_staff' => $available_staff
                    );
                }
            }
        }
        
        return $available_slots;
    }
    
    public static function cleanup_old_appointments() {
        global $wpdb;
        
        // Delete old available appointments (older than 30 days)
        $wpdb->delete(
            $wpdb->prefix . 'plandok_appointments',
            array(
                'status' => 'available',
                'appointment_date <' => date('Y-m-d', strtotime('-30 days'))
            ),
            array('%s', '%s')
        );
        
        // Archive old applications (older than 90 days)
        $wpdb->update(
            $wpdb->prefix . 'plandok_applications',
            array('status' => 'archived'),
            array(
                'status' => 'rejected',
                'submitted_at <' => date('Y-m-d H:i:s', strtotime('-90 days'))
            ),
            array('%s'),
            array('%s', '%s')
        );
    }
}

// Schedule cleanup task
add_action('plandok_daily_cleanup', array('PlandokDatabase', 'cleanup_old_appointments'));
