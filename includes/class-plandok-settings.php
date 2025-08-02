<?php

class PlandokSettings {
    
    public static function get_default_settings() {
        return array(
            'working_hours_start' => '09:00',
            'working_hours_end' => '18:00',
            'time_slot_duration' => 15,
            'booking_advance_days' => 30,
            'admin_email_notifications' => true,
            'customer_email_notifications' => true,
            'require_approval' => true,
            'allow_same_day_booking' => false,
            'booking_cutoff_hours' => 2,
            'max_bookings_per_day' => 0,
            'default_service_duration' => '30min',
            'currency_symbol' => '€',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'timezone' => wp_timezone_string(),
            'google_calendar_integration' => false,
            'reminder_email_hours' => 24,
            'cancellation_policy' => '',
            'booking_terms' => ''
        );
    }
    
    public static function get_setting($key, $default = null) {
        $settings = get_option('plandok_settings', self::get_default_settings());
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    
    public static function update_setting($key, $value) {
        $settings = get_option('plandok_settings', self::get_default_settings());
        $settings[$key] = $value;
        return update_option('plandok_settings', $settings);
    }
    
    public static function update_settings($new_settings) {
        $current_settings = get_option('plandok_settings', self::get_default_settings());
        $settings = array_merge($current_settings, $new_settings);
        return update_option('plandok_settings', $settings);
    }
    
    public static function reset_settings() {
        return update_option('plandok_settings', self::get_default_settings());
    }
    
    public static function get_working_hours() {
        return array(
            'start' => self::get_setting('working_hours_start', '09:00'),
            'end' => self::get_setting('working_hours_end', '18:00')
        );
    }
    
    public static function get_time_slots() {
        $working_hours = self::get_working_hours();
        $duration = self::get_setting('time_slot_duration', 15);
        
        $slots = array();
        $start_time = strtotime($working_hours['start']);
        $end_time = strtotime($working_hours['end']);
        
        for ($time = $start_time; $time < $end_time; $time += ($duration * 60)) {
            $slots[] = date('H:i', $time);
        }
        
        return $slots;
    }
    
    public static function is_booking_allowed($date) {
        $booking_date = new DateTime($date);
        $today = new DateTime('today');
        $advance_days = self::get_setting('booking_advance_days', 30);
        $max_date = new DateTime("+{$advance_days} days");
        
        // Check if date is not in the past
        if ($booking_date < $today) {
            return false;
        }
        
        // Check if same day booking is allowed
        if (!self::get_setting('allow_same_day_booking', false) && $booking_date == $today) {
            return false;
        }
        
        // Check advance booking limit
        if ($booking_date > $max_date) {
            return false;
        }
        
        return true;
    }
    
    public static function get_timezone() {
        return new DateTimeZone(self::get_setting('timezone', wp_timezone_string()));
    }
    
    public static function format_date($date, $format = null) {
        if (!$format) {
            $format = self::get_setting('date_format', 'Y-m-d');
        }
        
        if (is_string($date)) {
            $date = new DateTime($date, self::get_timezone());
        }
        
        return $date->format($format);
    }
    
    public static function format_time($time, $format = null) {
        if (!$format) {
            $format = self::get_setting('time_format', 'H:i');
        }
        
        if (is_string($time)) {
            $time = new DateTime($time, self::get_timezone());
        }
        
        return $time->format($format);
    }
    
    public static function get_currency_symbol() {
        return self::get_setting('currency_symbol', '€');
    }
    
    public static function format_price($price) {
        $symbol = self::get_currency_symbol();
        return $symbol . number_format($price, 2);
    }
}
