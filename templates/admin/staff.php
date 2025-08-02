<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap plandok-admin">
    <div class="plandok-header">
        <h1><?php _e('Staff Management', 'plandok-booking'); ?></h1>
    </div>

    <div class="plandok-card">
        <h3><?php _e('Add New Staff Member', 'plandok-booking'); ?></h3>
        <form id="staff-form" class="plandok-form">
            <input type="hidden" id="staff_id" name="id" value="">

            <div class="plandok-form-row">
                <div class="plandok-form-group">
                    <label for="name"><?php _e('Full Name', 'plandok-booking'); ?> *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="plandok-form-group">
                    <label for="email"><?php _e('Email Address', 'plandok-booking'); ?> *</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>

            <div class="plandok-form-row">
                <div class="plandok-form-group">
                    <label for="position"><?php _e('Position', 'plandok-booking'); ?> *</label>
                    <input type="text" id="position" name="position" required>
                </div>
                <div class="plandok-form-group">
                    <label for="phone"><?php _e('Phone Number', 'plandok-booking'); ?></label>
                    <input type="tel" id="phone" name="phone">
                </div>
            </div>

            <div class="plandok-form-group">
                <label>
                    <input type="checkbox" class="working-hours-toggle" id="has_working_hours" name="has_working_hours">
                    <?php _e('Set Custom Working Hours', 'plandok-booking'); ?>
                </label>
            </div>

            <div class="working-hours-fields" style="display: none;">
                <h4><?php _e('Working Hours', 'plandok-booking'); ?></h4>
                <div class="plandok-form-row">
                    <div class="plandok-form-group">
                        <label for="monday_hours"><?php _e('Monday', 'plandok-booking'); ?></label>
                        <input type="text" id="monday_hours" name="monday_hours" placeholder="09:00-18:00 or Closed">
                    </div>
                    <div class="plandok-form-group">
                        <label for="tuesday_hours"><?php _e('Tuesday', 'plandok-booking'); ?></label>
                        <input type="text" id="tuesday_hours" name="tuesday_hours" placeholder="09:00-18:00 or Closed">
                    </div>
                </div>
                <div class="plandok-form-row">
                    <div class="plandok-form-group">
                        <label for="wednesday_hours"><?php _e('Wednesday', 'plandok-booking'); ?></label>
                        <input type="text" id="wednesday_hours" name="wednesday_hours" placeholder="09:00-18:00 or Closed">
                    </div>
                    <div class="plandok-form-group">
                        <label for="thursday_hours"><?php _e('Thursday', 'plandok-booking'); ?></label>
                        <input type="text" id="thursday_hours" name="thursday_hours" placeholder="09:00-18:00 or Closed">
                    </div>
                </div>
                <div class="plandok-form-row">
                    <div class="plandok-form-group">
                        <label for="friday_hours"><?php _e('Friday', 'plandok-booking'); ?></label>
                        <input type="text" id="friday_hours" name="friday_hours" placeholder="09:00-18:00 or Closed">
                    </div>
                    <div class="plandok-form-group">
                        <label for="saturday_hours"><?php _e('Saturday', 'plandok-booking'); ?></label>
                        <input type="text" id="saturday_hours" name="saturday_hours" placeholder="09:00-18:00 or Closed">
                    </div>
                </div>
                <div class="plandok-form-group">
                    <label for="sunday_hours"><?php _e('Sunday', 'plandok-booking'); ?></label>
                    <input type="text" id="sunday_hours" name="sunday_hours" placeholder="09:00-18:00 or Closed">
                </div>
            </div>

            <div class="form-actions">
                <button type="button" onclick="resetStaffForm()" class="plandok-btn plandok-btn-secondary">
                    <?php _e('Reset', 'plandok-booking'); ?>
                </button>
                <button type="submit" class="plandok-btn">
                    <?php _e('Save Staff Member', 'plandok-booking'); ?>
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($staff)): ?>
    <div class="plandok-card">
        <h3><?php _e('Current Staff', 'plandok-booking'); ?></h3>
        <table class="plandok-table plandok-data-table">
            <thead>
                <tr>
                    <th><?php _e('Name', 'plandok-booking'); ?></th>
                    <th><?php _e('Position', 'plandok-booking'); ?></th>
                    <th><?php _e('Contact', 'plandok-booking'); ?></th>
                    <th><?php _e('Working Hours', 'plandok-booking'); ?></th>
                    <th><?php _e('Status', 'plandok-booking'); ?></th>
                    <th><?php _e('Actions', 'plandok-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff as $member): ?>
                <tr data-id="<?php echo esc_attr($member->id); ?>">
                    <td>
                        <strong><?php echo esc_html($member->name); ?></strong>
                    </td>
                    <td><?php echo esc_html($member->position); ?></td>
                    <td>
                        <a href="mailto:<?php echo esc_attr($member->email); ?>">
                            <?php echo esc_html($member->email); ?>
                        </a>
                        <?php if (!empty($member->phone)): ?>
                        <br><a href="tel:<?php echo esc_attr($member->phone); ?>">
                            <?php echo esc_html($member->phone); ?>
                        </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($member->working_hours)): ?>
                        <small><?php echo esc_html($member->working_hours); ?></small>
                        <?php else: ?>
                        <small><?php _e('Default hours', 'plandok-booking'); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo esc_attr($member->status); ?>">
                            <?php echo esc_html(ucfirst($member->status)); ?>
                        </span>
                    </td>
                    <td>
                        <button type="button" class="plandok-btn plandok-btn-small" onclick="editStaff(<?php echo esc_attr($member->id); ?>)">
                            <?php _e('Edit', 'plandok-booking'); ?>
                        </button>
                        <button type="button" class="plandok-btn plandok-btn-small plandok-btn-danger delete-btn"
                                data-type="staff" data-id="<?php echo esc_attr($member->id); ?>">
                            <?php _e('Delete', 'plandok-booking'); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
function resetStaffForm() {
    document.getElementById('staff-form').reset();
    document.getElementById('staff_id').value = '';
    document.querySelector('.working-hours-fields').style.display = 'none';
}

function editStaff(staffId) {
    // This would populate the form with staff data for editing
    // Implementation would depend on how you want to handle editing
    console.log('Edit staff:', staffId);
}
</script>
