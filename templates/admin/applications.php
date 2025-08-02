<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap plandok-admin">
    <div class="plandok-header">
        <h1><?php _e('Application Management', 'plandok-booking'); ?></h1>
    </div>

    <?php if (!empty($applications)): ?>
    <div class="plandok-card">
        <table class="plandok-table plandok-data-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th><?php _e('ID', 'plandok-booking'); ?></th>
                    <th><?php _e('Client', 'plandok-booking'); ?></th>
                    <th><?php _e('Contact', 'plandok-booking'); ?></th>
                    <th><?php _e('Service', 'plandok-booking'); ?></th>
                    <th><?php _e('Preferred Date/Time', 'plandok-booking'); ?></th>
                    <th><?php _e('Status', 'plandok-booking'); ?></th>
                    <th><?php _e('Applied', 'plandok-booking'); ?></th>
                    <th><?php _e('Actions', 'plandok-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                <tr data-id="<?php echo esc_attr($application->id); ?>">
                    <td><input type="checkbox" class="bulk-select" value="<?php echo esc_attr($application->id); ?>"></td>
                    <td><?php echo esc_html($application->id); ?></td>
                    <td>
                        <strong><?php echo esc_html($application->first_name . ' ' . $application->last_name); ?></strong>
                        <?php if (!empty($application->notes)): ?>
                        <br><small><?php echo esc_html(substr($application->notes, 0, 50)); ?>...</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($application->email)): ?>
                        <a href="mailto:<?php echo esc_attr($application->email); ?>">
                            <?php echo esc_html($application->email); ?>
                        </a><br>
                        <?php endif; ?>
                        <?php if (!empty($application->phone)): ?>
                        <a href="tel:<?php echo esc_attr($application->phone); ?>">
                            <?php echo esc_html($application->phone); ?>
                        </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $service = array_filter($services, function($s) use ($application) {
                            return $s->id == $application->service_id;
                        });
                        $service = reset($service);
                        echo $service ? esc_html($service->name) : __('Unknown Service', 'plandok-booking');
                        ?>
                    </td>
                    <td>
                        <?php echo esc_html($application->preferred_date); ?><br>
                        <small><?php echo esc_html($application->preferred_time); ?></small>
                    </td>
                    <td>
                        <select class="application-status" data-id="<?php echo esc_attr($application->id); ?>">
                            <option value="pending" <?php selected($application->status, 'pending'); ?>><?php _e('Pending', 'plandok-booking'); ?></option>
                            <option value="approved" <?php selected($application->status, 'approved'); ?>><?php _e('Approved', 'plandok-booking'); ?></option>
                            <option value="rejected" <?php selected($application->status, 'rejected'); ?>><?php _e('Rejected', 'plandok-booking'); ?></option>
                        </select>
                    </td>
                    <td><?php echo esc_html(date('M j, Y', strtotime($application->created_at))); ?></td>
                    <td>
                        <button type="button" class="plandok-btn plandok-btn-small plandok-btn-danger delete-btn"
                                data-type="application" data-id="<?php echo esc_attr($application->id); ?>">
                            <?php _e('Delete', 'plandok-booking'); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="bulk-actions">
            <select id="bulk-action-select">
                <option value=""><?php _e('Bulk Actions', 'plandok-booking'); ?></option>
                <option value="approve"><?php _e('Approve', 'plandok-booking'); ?></option>
                <option value="reject"><?php _e('Reject', 'plandok-booking'); ?></option>
                <option value="delete"><?php _e('Delete', 'plandok-booking'); ?></option>
            </select>
            <button type="button" id="bulk-action-apply" class="plandok-btn plandok-btn-secondary">
                <?php _e('Apply', 'plandok-booking'); ?>
            </button>
        </div>
    </div>
    <?php else: ?>
    <div class="plandok-card">
        <h3><?php _e('No Applications Found', 'plandok-booking'); ?></h3>
        <p><?php _e('There are currently no booking applications.', 'plandok-booking'); ?></p>
        <a href="<?php echo get_permalink(get_option('plandok_booking_page_id')); ?>" class="plandok-btn" target="_blank">
            <?php _e('View Booking Page', 'plandok-booking'); ?>
        </a>
    </div>
    <?php endif; ?>
</div>
