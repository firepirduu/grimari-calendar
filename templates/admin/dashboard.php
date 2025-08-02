<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap plandok-admin">
    <div class="plandok-header">
        <h1><?php _e('Plandok Dashboard', 'plandok-booking'); ?></h1>
    </div>

    <div class="plandok-dashboard-cards">
        <div class="plandok-card">
            <h3><?php _e('Today\'s Appointments', 'plandok-booking'); ?></h3>
            <div class="number"><?php echo count($upcoming_appointments); ?></div>
            <div class="label"><?php _e('appointments scheduled', 'plandok-booking'); ?></div>
        </div>

        <div class="plandok-card">
            <h3><?php _e('Total Services', 'plandok-booking'); ?></h3>
            <div class="number"><?php echo $total_services; ?></div>
            <div class="label"><?php _e('services available', 'plandok-booking'); ?></div>
        </div>

        <div class="plandok-card">
            <h3><?php _e('Staff Members', 'plandok-booking'); ?></h3>
            <div class="number"><?php echo $total_staff; ?></div>
            <div class="label"><?php _e('team members', 'plandok-booking'); ?></div>
        </div>

        <div class="plandok-card">
            <h3><?php _e('Pending Applications', 'plandok-booking'); ?></h3>
            <div class="number"><?php echo $pending_applications; ?></div>
            <div class="label"><?php _e('awaiting approval', 'plandok-booking'); ?></div>
        </div>
    </div>

    <?php if (!empty($upcoming_appointments)): ?>
    <div class="plandok-card">
        <h3><?php _e('Today\'s Appointments', 'plandok-booking'); ?></h3>
        <table class="plandok-table">
            <thead>
                <tr>
                    <th><?php _e('Time', 'plandok-booking'); ?></th>
                    <th><?php _e('Client', 'plandok-booking'); ?></th>
                    <th><?php _e('Service', 'plandok-booking'); ?></th>
                    <th><?php _e('Staff', 'plandok-booking'); ?></th>
                    <th><?php _e('Status', 'plandok-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming_appointments as $appointment): ?>
                <tr>
                    <td><?php echo esc_html($appointment->appointment_time); ?></td>
                    <td><?php echo esc_html($appointment->client_name); ?></td>
                    <td><?php echo esc_html($appointment->service_name); ?></td>
                    <td><?php echo esc_html($appointment->staff_name); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo esc_attr($appointment->status); ?>">
                            <?php echo esc_html(ucfirst($appointment->status)); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="plandok-card">
        <h3><?php _e('No Appointments Today', 'plandok-booking'); ?></h3>
        <p><?php _e('There are no appointments scheduled for today.', 'plandok-booking'); ?></p>
        <a href="<?php echo admin_url('admin.php?page=plandok-calendar'); ?>" class="plandok-btn">
            <?php _e('View Calendar', 'plandok-booking'); ?>
        </a>
    </div>
    <?php endif; ?>

    <div class="plandok-card">
        <h3><?php _e('Quick Actions', 'plandok-booking'); ?></h3>
        <p>
            <a href="<?php echo admin_url('admin.php?page=plandok-calendar'); ?>" class="plandok-btn">
                <?php _e('Manage Calendar', 'plandok-booking'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=plandok-services'); ?>" class="plandok-btn plandok-btn-secondary">
                <?php _e('Manage Services', 'plandok-booking'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=plandok-staff'); ?>" class="plandok-btn plandok-btn-secondary">
                <?php _e('Manage Staff', 'plandok-booking'); ?>
            </a>
            <a href="<?php echo get_permalink(get_option('plandok_booking_page_id')); ?>" class="plandok-btn plandok-btn-secondary" target="_blank">
                <?php _e('View Booking Page', 'plandok-booking'); ?>
            </a>
        </p>
    </div>
</div>
