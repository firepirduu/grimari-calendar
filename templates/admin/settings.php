<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap plandok-admin">
    <div class="plandok-header">
        <h1><?php _e('Plandok Settings', 'plandok-booking'); ?></h1>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field('plandok_settings', 'plandok_settings_nonce'); ?>
        
        <div class="plandok-card">
            <h3><?php _e('Working Hours', 'plandok-booking'); ?></h3>
            <div class="plandok-form-row">
                <div class="plandok-form-group">
                    <label for="working_hours_start"><?php _e('Start Time', 'plandok-booking'); ?></label>
                    <input type="time" id="working_hours_start" name="working_hours_start" 
                           value="<?php echo esc_attr($settings['working_hours_start'] ?? '09:00'); ?>">
                </div>
                <div class="plandok-form-group">
                    <label for="working_hours_end"><?php _e('End Time', 'plandok-booking'); ?></label>
                    <input type="time" id="working_hours_end" name="working_hours_end" 
                           value="<?php echo esc_attr($settings['working_hours_end'] ?? '18:00'); ?>">
                </div>
            </div>
        </div>

        <div class="plandok-card">
            <h3><?php _e('Booking Settings', 'plandok-booking'); ?></h3>
            <div class="plandok-form-row">
                <div class="plandok-form-group">
                    <label for="time_slot_duration"><?php _e('Time Slot Duration (minutes)', 'plandok-booking'); ?></label>
                    <select id="time_slot_duration" name="time_slot_duration">
                        <option value="15" <?php selected($settings['time_slot_duration'] ?? 15, 15); ?>>15 minutes</option>
                        <option value="30" <?php selected($settings['time_slot_duration'] ?? 15, 30); ?>>30 minutes</option>
                        <option value="60" <?php selected($settings['time_slot_duration'] ?? 15, 60); ?>>60 minutes</option>
                    </select>
                </div>
                <div class="plandok-form-group">
                    <label for="booking_advance_days"><?php _e('Booking Advance Days', 'plandok-booking'); ?></label>
                    <input type="number" id="booking_advance_days" name="booking_advance_days" min="1" max="365"
                           value="<?php echo esc_attr($settings['booking_advance_days'] ?? 30); ?>">
                    <small><?php _e('How many days in advance customers can book', 'plandok-booking'); ?></small>
                </div>
            </div>

            <div class="plandok-form-group">
                <label>
                    <input type="checkbox" name="require_approval" 
                           <?php checked($settings['require_approval'] ?? true, true); ?>>
                    <?php _e('Require admin approval for bookings', 'plandok-booking'); ?>
                </label>
            </div>
        </div>

        <div class="plandok-card">
            <h3><?php _e('Email Notifications', 'plandok-booking'); ?></h3>
            <div class="plandok-form-group">
                <label>
                    <input type="checkbox" name="admin_email_notifications" 
                           <?php checked($settings['admin_email_notifications'] ?? true, true); ?>>
                    <?php _e('Send email notifications to admin for new bookings', 'plandok-booking'); ?>
                </label>
            </div>
            <div class="plandok-form-group">
                <label>
                    <input type="checkbox" name="customer_email_notifications" 
                           <?php checked($settings['customer_email_notifications'] ?? true, true); ?>>
                    <?php _e('Send confirmation emails to customers', 'plandok-booking'); ?>
                </label>
            </div>
        </div>

        <div class="plandok-card">
            <h3><?php _e('Data Management', 'plandok-booking'); ?></h3>
            <div class="plandok-form-group">
                <label>
                    <input type="checkbox" name="remove_data_on_uninstall" 
                           <?php checked(get_option('plandok_remove_data_on_uninstall', false), true); ?>>
                    <?php _e('Remove all plugin data when uninstalling', 'plandok-booking'); ?>
                </label>
                <small style="color: #dc3232;">
                    <?php _e('Warning: This will permanently delete all bookings, services, staff, and applications when the plugin is uninstalled.', 'plandok-booking'); ?>
                </small>
            </div>
        </div>

        <div class="plandok-card">
            <h3><?php _e('Shortcodes', 'plandok-booking'); ?></h3>
            <p><?php _e('Use these shortcodes to display booking functionality on your pages:', 'plandok-booking'); ?></p>
            <div class="shortcode-list">
                <div class="shortcode-item">
                    <code>[plandok_booking_calendar]</code>
                    <span><?php _e('Display the full booking calendar', 'plandok-booking'); ?></span>
                </div>
                <div class="shortcode-item">
                    <code>[plandok_services]</code>
                    <span><?php _e('Display list of available services', 'plandok-booking'); ?></span>
                </div>
                <div class="shortcode-item">
                    <code>[plandok_staff]</code>
                    <span><?php _e('Display list of staff members', 'plandok-booking'); ?></span>
                </div>
            </div>
        </div>

        <div class="plandok-card">
            <h3><?php _e('System Information', 'plandok-booking'); ?></h3>
            <table class="plandok-table">
                <tbody>
                    <tr>
                        <td><?php _e('Plugin Version', 'plandok-booking'); ?></td>
                        <td><?php echo esc_html(PLANDOK_VERSION); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Database Version', 'plandok-booking'); ?></td>
                        <td><?php echo esc_html(get_option('plandok_db_version', '1.0.0')); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('WordPress Version', 'plandok-booking'); ?></td>
                        <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('PHP Version', 'plandok-booking'); ?></td>
                        <td><?php echo esc_html(PHP_VERSION); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Booking Page', 'plandok-booking'); ?></td>
                        <td>
                            <?php 
                            $page_id = get_option('plandok_booking_page_id');
                            if ($page_id && get_post($page_id)) {
                                echo '<a href="' . esc_url(get_permalink($page_id)) . '" target="_blank">' . 
                                     esc_html(get_the_title($page_id)) . '</a>';
                            } else {
                                echo __('Not found', 'plandok-booking');
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="submit">
            <input type="submit" name="submit" class="plandok-btn" value="<?php _e('Save Settings', 'plandok-booking'); ?>">
        </p>
    </form>
</div>

<style>
.shortcode-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.shortcode-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 10px;
    background: #f6f7f7;
    border-radius: 4px;
}

.shortcode-item code {
    background: #fff;
    padding: 5px 10px;
    border-radius: 3px;
    font-family: monospace;
    font-weight: bold;
    min-width: 200px;
}
</style>
