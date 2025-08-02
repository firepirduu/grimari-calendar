<?php

class PlandokEmail {
    
    public static function send_application_confirmation($application) {
        $settings = get_option('plandok_settings', array());
        
        if (!isset($settings['customer_email_notifications']) || !$settings['customer_email_notifications']) {
            return;
        }
        
        $to = $application->email;
        $subject = sprintf(__('Booking Application Received - %s', 'plandok-booking'), get_bloginfo('name'));
        
        $message = self::get_email_template('application_confirmation', array(
            'customer_name' => $application->first_name . ' ' . $application->last_name,
            'service_name' => $application->service_name,
            'preferred_date' => date_i18n(get_option('date_format'), strtotime($application->preferred_date)),
            'preferred_time' => date_i18n(get_option('time_format'), strtotime($application->preferred_time)),
            'phone' => $application->phone,
            'notes' => $application->notes,
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url()
        ));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($to, $subject, $message, $headers);
    }
    
    public static function send_admin_notification($application) {
        $settings = get_option('plandok_settings', array());
        
        if (!isset($settings['admin_email_notifications']) || !$settings['admin_email_notifications']) {
            return;
        }
        
        $admin_email = get_option('admin_email');
        $subject = sprintf(__('New Booking Application - %s', 'plandok-booking'), get_bloginfo('name'));
        
        $message = self::get_email_template('admin_notification', array(
            'customer_name' => $application->first_name . ' ' . $application->last_name,
            'email' => $application->email,
            'phone' => $application->phone,
            'service_name' => $application->service_name,
            'preferred_date' => date_i18n(get_option('date_format'), strtotime($application->preferred_date)),
            'preferred_time' => date_i18n(get_option('time_format'), strtotime($application->preferred_time)),
            'notes' => $application->notes,
            'admin_url' => admin_url('admin.php?page=plandok-applications'),
            'site_name' => get_bloginfo('name')
        ));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($admin_email, $subject, $message, $headers);
    }
    
    public static function send_application_status_update($application, $status) {
        $settings = get_option('plandok_settings', array());
        
        if (!isset($settings['customer_email_notifications']) || !$settings['customer_email_notifications']) {
            return;
        }
        
        $to = $application->email;
        $customer_name = $application->first_name . ' ' . $application->last_name;
        
        if ($status === 'approved') {
            $subject = sprintf(__('Appointment Approved - %s', 'plandok-booking'), get_bloginfo('name'));
            $template = 'application_approved';
        } elseif ($status === 'rejected') {
            $subject = sprintf(__('Appointment Update - %s', 'plandok-booking'), get_bloginfo('name'));
            $template = 'application_rejected';
        } else {
            return; // Don't send emails for other status changes
        }
        
        $message = self::get_email_template($template, array(
            'customer_name' => $customer_name,
            'service_name' => $application->service_name,
            'preferred_date' => date_i18n(get_option('date_format'), strtotime($application->preferred_date)),
            'preferred_time' => date_i18n(get_option('time_format'), strtotime($application->preferred_time)),
            'rejection_reason' => $application->rejection_reason,
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url(),
            'booking_url' => get_permalink(get_option('plandok_booking_page_id'))
        ));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($to, $subject, $message, $headers);
    }
    
    public static function send_appointment_reminder($appointment) {
        $settings = get_option('plandok_settings', array());
        
        if (!isset($settings['customer_email_notifications']) || !$settings['customer_email_notifications']) {
            return;
        }
        
        if (!$appointment->client_email) {
            return;
        }
        
        $to = $appointment->client_email;
        $subject = sprintf(__('Appointment Reminder - %s', 'plandok-booking'), get_bloginfo('name'));
        
        $message = self::get_email_template('appointment_reminder', array(
            'customer_name' => $appointment->client_name,
            'service_name' => $appointment->service_name,
            'appointment_date' => date_i18n(get_option('date_format'), strtotime($appointment->appointment_date)),
            'appointment_time' => date_i18n(get_option('time_format'), strtotime($appointment->appointment_time)),
            'duration' => $appointment->duration,
            'staff_name' => $appointment->staff_name,
            'location' => $appointment->location,
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url()
        ));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($to, $subject, $message, $headers);
    }
    
    private static function get_email_template($template, $variables) {
        $template_file = PLANDOK_PLUGIN_PATH . "templates/emails/{$template}.php";
        
        if (file_exists($template_file)) {
            ob_start();
            extract($variables);
            include $template_file;
            return ob_get_clean();
        }
        
        // Fallback to simple templates
        switch ($template) {
            case 'application_confirmation':
                return self::get_default_confirmation_template($variables);
            
            case 'admin_notification':
                return self::get_default_admin_template($variables);
            
            case 'application_approved':
                return self::get_default_approved_template($variables);
            
            case 'application_rejected':
                return self::get_default_rejected_template($variables);
            
            case 'appointment_reminder':
                return self::get_default_reminder_template($variables);
            
            default:
                return '';
        }
    }
    
    private static function get_default_confirmation_template($vars) {
        return sprintf(
            '<h2>%s</h2>
            <p>Dear %s,</p>
            <p>Thank you for your booking application. We have received your request and will contact you soon to confirm your appointment.</p>
            <h3>Application Details:</h3>
            <ul>
                <li><strong>Service:</strong> %s</li>
                <li><strong>Preferred Date:</strong> %s</li>
                <li><strong>Preferred Time:</strong> %s</li>
                <li><strong>Phone:</strong> %s</li>
                %s
            </ul>
            <p>We will contact you within 24 hours to confirm your appointment.</p>
            <p>Best regards,<br>%s Team</p>',
            __('Booking Application Received', 'plandok-booking'),
            esc_html($vars['customer_name']),
            esc_html($vars['service_name']),
            esc_html($vars['preferred_date']),
            esc_html($vars['preferred_time']),
            esc_html($vars['phone']),
            !empty($vars['notes']) ? '<li><strong>Notes:</strong> ' . esc_html($vars['notes']) . '</li>' : '',
            esc_html($vars['site_name'])
        );
    }
    
    private static function get_default_admin_template($vars) {
        return sprintf(
            '<h2>%s</h2>
            <p>A new booking application has been submitted:</p>
            <h3>Customer Details:</h3>
            <ul>
                <li><strong>Name:</strong> %s</li>
                <li><strong>Email:</strong> %s</li>
                <li><strong>Phone:</strong> %s</li>
            </ul>
            <h3>Appointment Details:</h3>
            <ul>
                <li><strong>Service:</strong> %s</li>
                <li><strong>Preferred Date:</strong> %s</li>
                <li><strong>Preferred Time:</strong> %s</li>
                %s
            </ul>
            <p><a href="%s">Manage Applications</a></p>',
            __('New Booking Application', 'plandok-booking'),
            esc_html($vars['customer_name']),
            esc_html($vars['email']),
            esc_html($vars['phone']),
            esc_html($vars['service_name']),
            esc_html($vars['preferred_date']),
            esc_html($vars['preferred_time']),
            !empty($vars['notes']) ? '<li><strong>Notes:</strong> ' . esc_html($vars['notes']) . '</li>' : '',
            esc_url($vars['admin_url'])
        );
    }
    
    private static function get_default_approved_template($vars) {
        return sprintf(
            '<h2>%s</h2>
            <p>Dear %s,</p>
            <p>Great news! Your appointment has been approved.</p>
            <h3>Appointment Details:</h3>
            <ul>
                <li><strong>Service:</strong> %s</li>
                <li><strong>Date:</strong> %s</li>
                <li><strong>Time:</strong> %s</li>
            </ul>
            <p>We look forward to seeing you!</p>
            <p>Best regards,<br>%s Team</p>',
            __('Appointment Approved', 'plandok-booking'),
            esc_html($vars['customer_name']),
            esc_html($vars['service_name']),
            esc_html($vars['preferred_date']),
            esc_html($vars['preferred_time']),
            esc_html($vars['site_name'])
        );
    }
    
    private static function get_default_rejected_template($vars) {
        return sprintf(
            '<h2>%s</h2>
            <p>Dear %s,</p>
            <p>We regret to inform you that we cannot accommodate your appointment request for %s on %s at %s.</p>
            %s
            <p>Please visit our <a href="%s">booking page</a> to see other available times.</p>
            <p>Thank you for your understanding.</p>
            <p>Best regards,<br>%s Team</p>',
            __('Appointment Update', 'plandok-booking'),
            esc_html($vars['customer_name']),
            esc_html($vars['service_name']),
            esc_html($vars['preferred_date']),
            esc_html($vars['preferred_time']),
            !empty($vars['rejection_reason']) ? '<p><strong>Reason:</strong> ' . esc_html($vars['rejection_reason']) . '</p>' : '',
            esc_url($vars['booking_url']),
            esc_html($vars['site_name'])
        );
    }
    
    private static function get_default_reminder_template($vars) {
        return sprintf(
            '<h2>%s</h2>
            <p>Dear %s,</p>
            <p>This is a reminder of your upcoming appointment:</p>
            <h3>Appointment Details:</h3>
            <ul>
                <li><strong>Service:</strong> %s</li>
                <li><strong>Date:</strong> %s</li>
                <li><strong>Time:</strong> %s</li>
                <li><strong>Duration:</strong> %s</li>
                <li><strong>Staff:</strong> %s</li>
                <li><strong>Location:</strong> %s</li>
            </ul>
            <p>We look forward to seeing you!</p>
            <p>Best regards,<br>%s Team</p>',
            __('Appointment Reminder', 'plandok-booking'),
            esc_html($vars['customer_name']),
            esc_html($vars['service_name']),
            esc_html($vars['appointment_date']),
            esc_html($vars['appointment_time']),
            esc_html($vars['duration']),
            esc_html($vars['staff_name']),
            esc_html($vars['location']),
            esc_html($vars['site_name'])
        );
    }
}
